<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JsonException;
use Laravel\Scout\Builder;
use OpenSearch\Client;
use RuntimeException;
use stdClass;

/**
 * @codeCoverageIgnore This whole service does many opensearch calls. Mocking the returns is not possible
 */
class StatementSearchService
{

    private string $index_name = 'statement_index';

    // This service builds and does queries with elastic.
    // The elastic has to be setup and there needs to be a 'statements' index.
    // The index needs to have all the fields

    // These are the filters that we are allowed to filter on.
    // If there is to be a new filter, then add it here first and then make
    // a function. new_attribute -> applyNewAttributeFilter()

    private array $allowed_filters = [
        's',
        'decision_visibility',
        'decision_monetary',
        'decision_provision',
        'decision_account',
        'account_type',
        'decision_ground',
        'category',
        'content_type',
        'source_type',
        'content_language',
        'automated_detection',
        'automated_decision',
        'platform_id',
        'territorial_scope',
        'category_specification',
    ];

    private array $allowed_aggregate_attributes = [
        'automated_decision',
        'automated_detection',
        'category',
        'content_type_single',
        'decision_account',
        'decision_ground',
        'decision_monetary',
        'decision_provision',
        'decision_visibility_single',
        'platform_id',
        'received_date',
        'source_type',
    ];

    private $mockCountQueryAnswer = 888;

    // When caching, go for 25 hours. Just so that there is a overlap.
    public const ONE_DAY = 25 * 60 * 60;
    public const ONE_HOUR = 1 * 60 * 60;
    public const FIVE_MINUTES = 5 * 60;

    public function __construct(private readonly Client $client, protected PlatformQueryService $platformQueryService)
    {
    }

    /**
     *
     * @param array $filters
     * @param array $options
     *
     * @return Builder
     */
    public function query(array $filters, array $options = []): Builder
    {
        $query = $this->buildQuery($filters);

        return $this->basicQuery($query, $options);
    }

    private function basicQuery(string $query, array $options = []): Builder
    {
        return Statement::search($query)->options($options);
    }


    private function buildQuery(array $filters): string
    {
        $queryAndParts = [];
        $query = '*';

        $current_env = config('app.env_real', '');

        if ($current_env === 'sandbox') {
            $filters['created_at_start'] = '01-04-2025';
        }

        if ($current_env === 'production') {
            $filters['created_at_start'] = '01-07-2025';
        }


        foreach ($this->allowed_filters as $filter_key) {
            if (isset($filters[$filter_key]) && $filters[$filter_key]) {
                $method = sprintf('apply%sFilter', ucfirst(Str::camel($filter_key)));
                $part = false;
                if (method_exists($this, $method)) {
                    $part = $this->$method($filters[$filter_key]);
                }

                if ($part) {
                    $queryAndParts[] = $part;
                }
            }
        }

        //dd($queryAndParts);

        // handle the date filters as needed.
        $created_at_filter = $this->applyCreatedAtFilter($filters);
        if ($created_at_filter !== '' && $created_at_filter !== '0') {
            $queryAndParts[] = $created_at_filter;
        }

        // if we have parts, then glue them together with AND
        if ($queryAndParts !== []) {
            $query = "(" . implode(") AND (", $queryAndParts) . ")";
        }


        if (config('scout.driver', '') === 'database' && config('app.env') !== 'testing') {
            // @codeCoverageIgnoreStart
            $query = $filters['s'] ?? '';
            // @codeCoverageIgnoreEnd
        }

        return $query;
    }

    private function applyCreatedAtFilter(array $filters): string
    {
        try {
            // Start but no end.
            if (($filters['created_at_start'] ?? false) && !($filters['created_at_end'] ?? false)) {
                $now = date('Y-m-d\TH:i:s');
                $start = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_start'] . ' 00:00:00');

                return 'created_at:[' . $start->format('Y-m-d\TH:i:s') . ' TO ' . $now . ']';
            }

            // End but no start.
            if (($filters['created_at_end'] ?? false) && !($filters['created_at_start'] ?? false)) {
                $beginning = date('Y-m-d\TH:i:s', strtotime('2020-01-01'));
                $end = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_end'] . ' 23:59:59');

                return 'created_at:[' . $beginning . ' TO ' . $end->format('Y-m-d\TH:i:s') . ']';
            }

            // both start and end.
            if (($filters['created_at_start'] ?? false) && ($filters['created_at_end'] ?? false)) {
                $start = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_start'] . ' 00:00:00');
                $end = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_end'] . ' 23:59:59');

                return 'created_at:[' . $start->format('Y-m-d\TH:i:s') . ' TO ' . $end->format('Y-m-d\TH:i:s') . ']';
            }
        } catch (Exception) {
            // Most likely the date supplied for the start or the end was bad.
            return '';
        }

        // Normally we don't get here.
        return '';
    }

    /**
     * @return string
     */
    private function applySFilter(string $filter_value): string
    {
        $filter_value = preg_replace("/[^a-zA-Z0-9\ \-\_]+/", "", $filter_value);
        $textfields = [
            'decision_visibility_other',
            'decision_monetary_other',
            'illegal_content_legal_ground',
            'illegal_content_explanation',
            'incompatible_content_ground',
            'incompatible_content_explanation',
            'decision_facts',
            'content_type_other',
            'source_identity',
            'uuid',
            'puid',
            'content_id_ean'
        ];

        $ors = [];
        foreach ($textfields as $textfield) {
            $ors[] = $textfield . ':"' . $filter_value . '"';
        }

        if (config('scout.driver', '') === 'database' && config('app.env', '') !== 'testing') {
            // @codeCoverageIgnoreStart
            return $filter_value;
            // @codeCoverageIgnoreEnd
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionVisibilityFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-decision_visibility:?*)';
        }

        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_VISIBILITIES));
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_visibility:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionMonetaryFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-decision_monetary:?*)';
        }

        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_MONETARIES));
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_monetary:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionProvisionFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-decision_provision:?*)';
        }

        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_PROVISIONS));
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_provision:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyTerritorialScopeFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-territorial_scope:?*)';
        }

        $filter_values = array_intersect($filter_values, EuropeanCountriesService::EUROPEAN_COUNTRY_CODES);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'territorial_scope:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }


    private function applyDecisionAccountFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-decision_account:?*)';
        }
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_ACCOUNTS));

        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_account:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyAccountTypeFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-account_type:?*)';
        }
        $filter_values = array_intersect($filter_values, array_keys(Statement::ACCOUNT_TYPES));
        foreach ($filter_values as $filter_value) {
            $ors[] = 'account_type:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyCategorySpecificationFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-category_specification:?*)';
        }
        $filter_values = array_intersect($filter_values, array_keys(Statement::KEYWORDS));
        foreach ($filter_values as $filter_value) {
            $ors[] = 'category_specification:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyDecisionGroundFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-decision_ground:?*)';
        }
        $filter_values = array_intersect($filter_values, array_keys(Statement::DECISION_GROUNDS));
        foreach ($filter_values as $filter_value) {
            $ors[] = 'decision_ground:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyCategoryFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-category:?*)';
        }
        $filter_values = array_intersect($filter_values, array_keys(Statement::STATEMENT_CATEGORIES));
        foreach ($filter_values as $filter_value) {
            $ors[] = 'category:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyContentTypeFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-content_type:?*)';
        }
        $filter_values = array_intersect($filter_values, array_keys(Statement::CONTENT_TYPES));
        foreach ($filter_values as $filter_value) {
            $ors[] = 'content_type:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    /**
     * @codeCoverageIgnore
     * @param array $filter_values
     * @return string
     */
    private function applyContentLanguageFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-content_language:?*)';
        }
        $all_isos = array_keys(EuropeanLanguagesService::ALL_LANGUAGES);
        $filter_values = array_intersect($filter_values, $all_isos);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'content_language:"' . $filter_value . '"';
        }

        return implode(' OR ', $ors);
    }

    private function applyAutomatedDetectionFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-automated_detection:?*)';
        }
        $filter_values = array_intersect($filter_values, Statement::AUTOMATED_DETECTIONS);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'automated_detection:' . ($filter_value === Statement::AUTOMATED_DETECTION_YES ? 'true' : 'false');
        }

        return implode(' OR ', $ors);
    }

    private function applyAutomatedDecisionFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-automated_decision:?*)';
        }
        $filter_values = array_intersect($filter_values, array_keys(Statement::AUTOMATED_DECISIONS));
        foreach ($filter_values as $filter_value) {
            $ors[] = 'automated_decision:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    /**
     * @return string
     */
    private function applyPlatformIdFilter(array $filter_values): string
    {
        $ors = [];
        $platform_ids = $this->platformQueryService->getPlatformIds();
        $filter_values = array_intersect($platform_ids, $filter_values);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'platform_id:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applySourceTypeFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-source_type:?*)';
        }
        $filter_values = array_intersect($filter_values, array_keys(Statement::SOURCE_TYPES));

        foreach ($filter_values as $filter_value) {
            $ors[] = 'source_type:' . $filter_value;
        }

        return implode(' OR ', $ors);
    }


    /**
     * @codeCoverageIgnore This is calling opensearch directly
     * @param \Illuminate\Database\Eloquent\Collection $statements
     * @return void
     */
    public function bulkIndexStatements(Collection $statements): void
    {
        if ($statements->count() !== 0 && config('scout.driver') === 'opensearch') {
            $bulk = [];
            /** @var Statement $statement */
            foreach ($statements as $statement) {
                $doc = $statement->toSearchableArray();
                $bulk[] = json_encode([
                    'index' => [
                        '_index' => $this->index_name,
                        '_id' => $statement->id,
                    ],
                ], JSON_THROW_ON_ERROR);
                $bulk[] = json_encode($doc, JSON_THROW_ON_ERROR);
            }

            // Call the bulk and make them searchable.
            $this->client->bulk(['require_alias' => true, 'body' => implode("\n", $bulk)]);
        }
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public function allSendingPlatformIds(): array
    {
        return Cache::remember('all_sending_platform_ids', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_platform_ids = [];
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                foreach ($methods as $method => $total) {
                    if ($total) {
                        $sending_platform_ids[] = $platform_id;
                        break;
                    }
                }
            }
            return $sending_platform_ids;
        });
    }

    public function totalNonVlopPlatformsSending(): int
    {
        return Cache::remember('total_sending_non_vlop_platforms', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_non_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if (!in_array($platform_id, $vlop_ids, true)) {
                    foreach ($methods as $method => $total) {
                        if ($total) {
                            $sending_non_vlop_platform_ids[] = $platform_id;
                            break;
                        }
                    }
                }
            }
            return count($sending_non_vlop_platform_ids);
        });
    }

    public function totalNonVlopPlatformsSendingApi(): int
    {
        return Cache::remember('total_sending_non_vlop_platforms_api', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_api_non_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if (($methods[Statement::METHOD_API] || $methods[Statement::METHOD_API_MULTI]) && !in_array($platform_id, $vlop_ids, true)) {
                    $sending_api_non_vlop_platform_ids[] = $platform_id;
                }
            }
            return count($sending_api_non_vlop_platform_ids);
        });
    }

    public function totalNonVlopPlatformsSendingWebform(): int
    {
        return Cache::remember('total_sending_non_vlop_platforms_webform', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_webform_non_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if ($methods[Statement::METHOD_FORM] && !in_array($platform_id, $vlop_ids, true)) {
                    $sending_webform_non_vlop_platform_ids[] = $platform_id;
                }
            }
            return count($sending_webform_non_vlop_platform_ids);
        });
    }

    public function totalVlopPlatformsSending(): int
    {
        return Cache::remember('total_sending_vlop_platforms', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if (in_array($platform_id, $vlop_ids, true)) {
                    foreach ($methods as $method => $total) {
                        if ($total) {
                            $sending_vlop_platform_ids[] = $platform_id;
                            break;
                        }
                    }
                }
            }
            return count($sending_vlop_platform_ids);
        });
    }

    public function totalVlopPlatformsSendingApi(): int
    {
        return Cache::remember('total_sending_vlop_platforms_api', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_api_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if (($methods[Statement::METHOD_API] || $methods[Statement::METHOD_API_MULTI]) && in_array($platform_id, $vlop_ids, true)) {
                    $sending_api_vlop_platform_ids[] = $platform_id;
                }
            }
            return count($sending_api_vlop_platform_ids);
        });
    }

    public function totalVlopPlatformsSendingWebform(): int
    {
        return Cache::remember('total_sending_vlop_platforms_webform', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_webform_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if ($methods[Statement::METHOD_FORM] && in_array($platform_id, $vlop_ids, true)) {
                    $sending_webform_vlop_platform_ids[] = $platform_id;
                }
            }
            return count($sending_webform_vlop_platform_ids);
        });
    }

    private function vlopIds(): array
    {
        return $this->platformQueryService->getVlopPlatformIds();
    }

    public function startCountQuery(): string
    {
        return "SELECT CAST(count(*) AS BIGINT) as count FROM " . $this->index_name;
    }

    public function buildWheres(array $conditions): string
    {
        if ($conditions !== []) {
            return " WHERE " . implode(" AND ", $conditions);
        }

        return '';
    }

    public function extractCountQueryResult($result): int
    {
        return (int) ($result['datarows'][0][0] ?? 0);
    }

    public function runSql(string $sql): array
    {
        if (config('scout.driver') === 'opensearch') {
            return $this->client->sql()->query([
                'query' => $sql,
            ]);
        }

        return $this->mockCountQueryResult();
    }

    public function runAndExtractCountQuerySql(string $sql): int
    {
        return $this->extractCountQueryResult($this->runSql($sql));
    }

    public function mockCountQueryResult(): array
    {
        return [
            'datarows' => [
                [
                    $this->mockCountQueryAnswer,
                ],
            ],
        ];
    }

    public function setMockCountQueryAnswer(int $answer): void
    {
        $this->mockCountQueryAnswer = $answer;
    }

    public function getCountQueryResult(array $conditions = []): int
    {
        return $this->extractCountQueryResult($this->runSql($this->startCountQuery() . $this->buildWheres($conditions)));
    }

    public function highestId(): int
    {
        $sql = "SELECT max(id) AS max_id FROM " . $this->index_name;
        $result = $this->runSql($sql);
        return (int) ($result['datarows'][0][0] ?? 0);
    }

    public function grandTotal(): int
    {
        return Cache::remember('grand_total', self::ONE_DAY, fn() => $this->grandTotalNoCache());
    }

    public function grandTotalNoCache(): int
    {
        return $this->getCountQueryResult();
    }

    public function receivedDateCondition(Carbon $date): string
    {
        return "received_date = '" . $date->format('Y-m-d') . "'";
    }

    public function totalForDate(Carbon $date): int
    {
        return $this->getCountQueryResult([$this->receivedDateCondition($date)]);
    }

    public function totalForPlatformDate(Platform $platform, Carbon $date): int
    {
        return $this->getCountQueryResult([
            "platform_id = " . $platform->id,
            $this->receivedDateCondition($date),
        ]);
    }

    public function totalsForPlatformsDate(Carbon $date): array
    {
        $aggregates = $this->processDateAggregate($date, ['platform_id']);
        return $aggregates['aggregates'];
    }

    public function methodsByPlatformsDate(Carbon $date): array
    {
        $query = "SELECT COUNT(*), method, platform_id FROM " . $this->index_name . " WHERE received_date = '" . $date->format('Y-m-d') . " 00:00:00' GROUP BY platform_id, method LIMIT 5000";
        return $this->extractMethodAggregateFromQuery($query);
    }

    public function methodsByPlatformAll(): array
    {
        return Cache::remember('methods_by_platform_all', self::ONE_HOUR, function () {
            $dsa_team_platform_id = Platform::dsaTeamPlatformId();
            $query = "SELECT CAST(count(*) AS BIGINT), method, platform_id FROM " . $this->index_name . " WHERE platform_id <> " . $dsa_team_platform_id . " GROUP BY platform_id, method LIMIT 5000";
            return $this->extractMethodAggregateFromQuery($query);
        });

    }

    private function extractMethodAggregateFromQuery(string $query): array
    {
        $results = $this->runSql($query);
        $rows = $results['datarows'];
        $out = [];
        if (config('scout.driver') === 'opensearch') {
            foreach ($rows as [$total, $method, $platform_id]) {
                $out[$platform_id][$method] = $total;
            }
        }

        foreach ($out as $platform_id => $methods) {
            $out[$platform_id][Statement::METHOD_FORM] ??= 0;
            $out[$platform_id][Statement::METHOD_API] ??= 0;
            $out[$platform_id][Statement::METHOD_API_MULTI] ??= 0;
        }

        return $out;
    }

    public function totalForPlatformIdAndMethod(int $platform_id, string $method): int
    {
        $totals = $this->methodsByPlatformAll();
        return $totals[$platform_id][$method] ?? 0;
    }

    public function receivedDateRangeCondition(Carbon $start, Carbon $end): string
    {
        return "received_date BETWEEN '" . $start->format('Y-m-d') . "' AND '" . $end->format('Y-m-d') . "'";
    }

    public function totalForDateRange(Carbon $start, Carbon $end): int
    {
        return $this->getCountQueryResult([$this->receivedDateRangeCondition($start, $end)]);
    }

    public function datesTotalsForRange(Carbon $start, Carbon $end): array
    {
        $prepare = [];
        $current = $start->clone();
        while ($current <= $end) {
            $prepare[$current->format('Y-m-d')] = 0;
            $current->addDay();
        }

        $results = $this->processRangeAggregate($start, $end, ['received_date']);

        foreach ($results['aggregates'] as $aggregate) {
            $prepare[$aggregate['received_date']] = $aggregate['total'];
        }

        return array_map(static fn($date, $total) => [
            'date' => $date,
            'total' => $total,
        ], array_keys($prepare), array_values($prepare));
    }

    /**
     * @return array
     */
    public function topCategories(): array
    {
        return Cache::remember('top_categories', self::ONE_DAY, fn() => $this->topCategoriesNoCache());
    }

    public function topCategoriesNoCache(): array
    {
        $results = [];
        $categories = array_keys(Statement::STATEMENT_CATEGORIES);
        foreach ($categories as $category) {
            $results[] = [
                'value' => $category,
                'total' => $this->getCountQueryResult(["category = '" . $category . "'"]),
            ];
        }

        uasort($results, static fn($a, $b) => ($a['total'] <=> $b['total']) * -1);

        return $results;
    }

    /**
     * @return array
     */
    public function topDecisionVisibilities(): array
    {
        return Cache::remember('top_decisions_visibility', self::ONE_DAY, fn() => $this->topDecisionVisibilitiesNoCache());
    }

    public function topDecisionVisibilitiesNoCache(): array
    {
        $results = [];
        $decision_visibilities = array_keys(Statement::DECISION_VISIBILITIES);
        foreach ($decision_visibilities as $decision_visibility) {
            $results[] = [
                'value' => $decision_visibility,
                'total' => $this->getCountQueryResult(["decision_visibility_single = '" . $decision_visibility . "'"]),
            ];
        }

        uasort($results, static fn($a, $b) => ($a['total'] <=> $b['total']) * -1);

        return $results;
    }

    /**
     * @return int
     */
    public function fullyAutomatedDecisionPercentage(): int
    {
        return Cache::remember('automated_decisions_percentage', self::ONE_DAY, fn() => $this->fullyAutomatedDecisionPercentageNoCache());
    }

    public function fullyAutomatedDecisionPercentageNoCache(): int
    {
        $automated_decision_count = $this->getCountQueryResult(["automated_decision = 'AUTOMATED_DECISION_FULLY'"]);
        $total = $this->grandTotal();

        return round((($automated_decision_count / max(1, $total)) * 100));
    }

    /**
     * @param $key
     *
     * @return void
     */
    public function pushOSAKey($key): void
    {
        $keys = Cache::get('osa_cache', []);
        $keys[] = $key;
        Cache::forever('osa_cache', array_unique($keys));
    }

    public function uuidToId(string $uuid): int
    {
        $query = [
            "size" => 1,
            "query" => [
                "bool" => [
                    "must" => [
                        [
                            "match_phrase" => [
                                "uuid" => $uuid,
                            ],
                        ],
                    ],
                ],
            ],
            "_source" => [
                "includes" => [
                    "id",
                ],
                "excludes" => [],
            ],
        ];

        $result = $this->client->search([
            'index' => $this->index_name,
            'body' => $query,
        ]);

        return $result['hits']['hits'][0]['_source']['id'] ?? 0;
    }

    public function PlatformIdPuidToId(int $platform_id, string $puid): int
    {
        return $this->PlatformIdPuidToIds($platform_id, $puid)[0] ?? 0;
    }

    public function PlatformIdPuidToIds(int $platform_id, string $puid): array
    {
        $puid = str_replace("=", ".", $puid);
        $query = [
            "size" => 1000,
            "query" => [
                "bool" => [
                    "must" => [
                        [
                            "match_phrase" => [
                                "puid" => $puid,
                            ],
                        ],
                        [
                            "match" => [
                                "platform_id" => $platform_id,
                            ],
                        ],
                    ],
                ],
            ],
            "_source" => [
                "includes" => [
                    "id",
                ],
                "excludes" => [],
            ],
        ];

        $result = $this->client->search([
            'index' => $this->index_name,
            'body' => $query,
        ]);

        return array_map(static fn($hit) => $hit['_id'], $result['hits']['hits'] ?? []);
    }


    public function clearOSACache(): void
    {
        $keys = Cache::get('osa_cache', []);
        foreach ($keys as $key) {
            Cache::delete($key);
        }

        Cache::delete('osa_cache');
    }

    public function processRangeAggregate(Carbon $start, Carbon $end, array $attributes, bool $caching = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'osar__' . $start->format('Y-m-d') . '__' . $end->format('Y-m-d') . '__' . implode('__', $attributes);

        if (!$caching) {
            Cache::delete($key);
        }

        $cache = 'hit';
        $results = Cache::rememberForever($key, function () use ($start, $end, $attributes, $key, &$cache) {
            $query = $this->aggregateQueryRange($start, $end, $attributes);
            $cache = 'miss';
            $this->pushOSAKey($key);

            return $this->processAggregateQuery($query);
        });

        $timeend = microtime(true);
        $timediff = $timeend - $timestart;


        $results['dates'] = [$start->format('Y-m-d'), $end->format('Y-m-d')];
        $results['attributes'] = $attributes;
        $results['key'] = $key;
        $results['cache'] = $cache;
        $results['duration'] = (float) number_format($timediff, 4);

        return $results;
    }

    public function processDatesAggregate(Carbon $start, Carbon $end, array $attributes, bool $caching = true, bool $daycache = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'osad__' . $start->format('Y-m-d') . '__' . $end->format('Y-m-d') . '__' . implode('__', $attributes);

        if (!$caching) {
            Cache::delete($key);
        }

        $cache = 'hit';
        $days = Cache::rememberForever($key, function () use ($start, $end, $attributes, $daycache, $key, &$cache) {
            $days = [];
            $current = $end->clone();

            while ($current >= $start) {
                $days[] = $this->processDateAggregate($current, $attributes, $daycache);
                $current->subDay();
            }

            $cache = 'miss';
            $this->pushOSAKey($key);

            return $days;
        });

        $total = array_sum(array_map(static fn($day) => $day['total'], $days));

        $timeend = microtime(true);
        $timediff = $timeend - $timestart;

        $results['days'] = $days;
        $results['total'] = $total;
        $results['dates'] = [$start->format('Y-m-d'), $end->format('Y-m-d')];
        $results['attributes'] = $attributes;
        $results['key'] = $key;
        $results['cache'] = $cache;
        $results['duration'] = (float) number_format($timediff, 4);

        return $results;
    }

    public function processDateAggregate(Carbon $date, array $attributes, bool $caching = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'osa__' . $date->format('Y-m-d') . '__' . implode('__', $attributes);

        if ($date > Carbon::yesterday()) {
            throw new RuntimeException('aggregates must done on dates in the past');
        }

        if (!$caching) {
            Cache::delete($key);
        }

        $cache = 'hit';
        $results = Cache::rememberForever($key, function () use ($date, $attributes, $key, &$cache) {
            $query = $this->aggregateQuerySingleDate($date, $attributes);
            $cache = 'miss';
            $this->pushOSAKey($key);

            return $this->processAggregateQuery($query);
        });

        $timeend = microtime(true);
        $timediff = $timeend - $timestart;

        $results['date'] = $date->format('Y-m-d');
        $results['attributes'] = $attributes;
        $results['key'] = $key;
        $results['cache'] = $cache;
        $results['duration'] = (float) number_format($timediff, 4);

        return $results;
    }

    public function getAllowedAggregateAttributes(bool $remove_received_date = false): array
    {
        $out = $this->allowed_aggregate_attributes;
        if ($remove_received_date) {
            $out = array_diff($out, ['received_date']);
        }

        return $out;
    }

    /**
     * @throws JsonException
     */
    private function aggregateQueryRange(Carbon $start, Carbon $end, $attributes)
    {
        $query_string = <<<JSON
{
  "from": 0,
  "size": 0,
  "timeout": "1m",
  "query": {
    "bool": {
      "filter": [
        {
          "range": {
            "created_at": {
              "from": null,
              "to": null,
              "include_lower": true,
              "include_upper": true,
              "boost": 1.0
            }
          }
        }
      ],
      "adjust_pure_negative": true,
      "boost": 1.0
    }
  },
  "aggregations": {
    "composite_buckets": {
      "composite": {
        "size": 5000,
        "sources": []
      },
      "aggregations": {
        "count(*)": {
          "value_count": {
            "field": "_index"
          }
        }
      }
    }
  }
}
JSON;
        $query = json_decode($query_string, false, 512, JSON_THROW_ON_ERROR);

        $start->hour = 0;
        $start->minute = 0;
        $start->second = 0;

        $end->hour = 23;
        $end->minute = 59;
        $end->second = 59;

        $query->query->bool->filter[0]->range->created_at->from = $start->getTimestampMs();
        $query->query->bool->filter[0]->range->created_at->to = $end->getTimestampMs();

        $sources = [];
        foreach ($attributes as $attribute) {
            $sources[] = $this->aggregateQueryBucket($attribute);
        }

        if ($sources === []) {
            $sources[] = $this->aggregateQueryBucket('received_date');
        }

        $query->aggregations->composite_buckets->composite->sources = $sources;

        return $query;
    }

    /**
     * @throws JsonException
     */
    private function aggregateQuerySingleDate(Carbon $date, $attributes)
    {
        $query_string = <<<JSON
{
  "from": 0,
  "size": 0,
  "timeout": "1m",
  "query": {
    "term": {
      "received_date": {
        "value": null,
        "boost": 1.0
      }
    }
  },
  "aggregations": {
    "composite_buckets": {
      "composite": {
        "size": 5000,
        "sources": []
      },
      "aggregations": {
        "count(*)": {
          "value_count": {
            "field": "_index"
          }
        }
      }
    }
  }
}
JSON;
        $query = json_decode($query_string, false, 512, JSON_THROW_ON_ERROR);

        $date->hour = 0;
        $date->minute = 0;
        $date->second = 0;

        $query->query->term->received_date->value = $date->getTimestampMs();

        $sources = [];
        foreach ($attributes as $attribute) {
            $sources[] = $this->aggregateQueryBucket($attribute);
        }

        if ($sources === []) {
            $sources[] = $this->aggregateQueryBucket('received_date');
        }

        $query->aggregations->composite_buckets->composite->sources = $sources;

        return $query;
    }

    private function aggregateQueryBucket($attribute): stdClass
    {
        $source = new stdClass();
        $source->$attribute = new stdClass();
        $source->$attribute->terms = new stdClass();
        $source->$attribute->terms->field = $attribute;
        $source->$attribute->terms->missing_bucket = true;
        $source->$attribute->terms->missing_order = "first";
        $source->$attribute->terms->order = "asc";

        return $source;
    }

    /**
     * @return array
     */
    public function processAggregateQuery(stdClass $query): array
    {
        $result = $this->client->search([
            'index' => $this->index_name,
            'body' => $query,
        ]);
        $buckets = $result['aggregations']['composite_buckets']['buckets'];

        $platforms = [];
        // Do we need platforms
        if ($buckets[0]['key']['platform_id'] ?? false) {
            $platforms = $this->platformQueryService->getPlatformsById();
        }

        $out = [];
        $total = 0;
        $total_aggregates = 0;
        foreach ($buckets as $bucket) {
            $item = [];
            $attributes = $bucket['key'];

            // Manipulate the results
            if (isset($attributes['automated_detection'])) {
                $attributes['automated_detection'] = (int) $attributes['automated_detection'];
            }

            if (isset($attributes['received_date'])) {
                $attributes['received_date'] = date('Y-m-d', ($attributes['received_date'] / 1000));
            }

            // Put the attributes on the root item
            foreach ($attributes as $key => $value) {
                $item[$key] = $value;
            }

            // build a permutation string
            $item['permutation'] = implode(',', array_map(static fn($key, $value) => $key . ":" . $value, array_keys($attributes), array_values($attributes)));

            // add the platform name on at the end if we need to.
            if (isset($item['platform_id'])) {
                // yes we will need it.
                $item['platform_name'] = $platforms[$item['platform_id']] ?? '';
            }


            $item['total'] = $bucket['doc_count'];
            $total += $bucket['doc_count'];
            ++$total_aggregates;
            $out[] = $item;
        }

        return ['aggregates' => $out, 'total' => $total, 'total_aggregates' => $total_aggregates];
    }

    public function sanitizeAggregateAttributes(array &$attributes): void
    {
        sort($attributes);
        $attributes = array_intersect($attributes, $this->allowed_aggregate_attributes);
        $attributes = array_unique($attributes);
        if ($attributes === []) {
            $attributes[] = 'received_date';
        }
    }

    public function statementIndexProperties(): array
    {
        return [
            'properties' =>
                [
                    'automated_decision' =>
                        [
                            'type' => 'keyword',
                        ],
                    'automated_detection' =>
                        [
                            'type' => 'boolean',
                        ],
                    'category' =>
                        [
                            'type' => 'keyword',
                        ],
                    'category_specification' =>
                        [
                            'type' => 'text',
                        ],
                    'content_type' =>
                        [
                            'type' => 'text',
                        ],
                    'content_type_single' =>
                        [
                            'type' => 'keyword',
                        ],
                    'content_type_other' =>
                        [
                            'type' => 'text',
                        ],
                    'content_language' =>
                        [
                            'type' => 'keyword',
                        ],
                    'created_at' =>
                        [
                            'type' => 'date',
                        ],
                    'received_date' =>
                        [
                            'type' => 'date',
                        ],
                    'content_date' =>
                        [
                            'type' => 'date',
                        ],
                    'application_date' =>
                        [
                            'type' => 'date',
                        ],
                    'decision_account' =>
                        [
                            'type' => 'keyword',
                        ],
                    'account_type' =>
                        [
                            'type' => 'keyword',
                        ],
                    'decision_facts' =>
                        [
                            'type' => 'text',
                        ],
                    'decision_ground' =>
                        [
                            'type' => 'keyword',
                        ],
                    'decision_monetary' =>
                        [
                            'type' => 'keyword',
                        ],
                    'decision_provision' =>
                        [
                            'type' => 'keyword',
                        ],
                    'decision_visibility' =>
                        [
                            'type' => 'text',
                        ],
                    'decision_visibility_single' =>
                        [
                            'type' => 'keyword',
                        ],
                    'id' =>
                        [
                            'type' => 'long',
                        ],
                    'illegal_content_explanation' =>
                        [
                            'type' => 'text',
                        ],
                    'illegal_content_legal_ground' =>
                        [
                            'type' => 'text',
                        ],
                    'incompatible_content_explanation' =>
                        [
                            'type' => 'text',
                        ],
                    'incompatible_content_ground' =>
                        [
                            'type' => 'text',
                        ],
                    'platform_id' =>
                        [
                            'type' => 'long',
                        ],
                    'platform_name' =>
                        [
                            'type' => 'text',
                            "fields" => [
                                "keyword" => [
                                    "type" => "keyword",
                                    "ignore_above" => 256
                                ]
                            ]
                        ],
                    'platform_uuid' =>
                        [
                            'type' => 'text',
                        ],
                    'source_identity' =>
                        [
                            'type' => 'text',
                        ],
                    'source_type' =>
                        [
                            'type' => 'keyword',
                        ],
                    'url' =>
                        [
                            'type' => 'text',
                        ],
                    'uuid' =>
                        [
                            'type' => 'text',
                        ],
                    'puid' =>
                        [
                            'type' => 'text',
                        ],
                    'decision_visibility_other' =>
                        [
                            'type' => 'text',
                        ],
                    'decision_monetary_other' =>
                        [
                            'type' => 'text',
                        ],
                    'territorial_scope' =>
                        [
                            'type' => 'text',
                        ],
                    'method' =>
                        [
                            'type' => 'keyword',
                        ],
                    'content_id_ean' =>
                        [
                            'type' => 'long',
                        ],
                ],
        ];
    }
}
