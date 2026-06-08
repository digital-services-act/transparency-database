<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Elastic\Elasticsearch\Client;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore This whole service does many elasticsearch calls. Mocking the returns is not possible
 */
class StatementElasticSearchService
{
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

    private $mockCountQueryAnswer = 888;

    // When caching, go for 25 hours. Just so that there is a overlap.
    public const ONE_DAY = 25 * 60 * 60;

    public const ONE_HOUR = 1 * 60 * 60;

    public const FIVE_MINUTES = 5 * 60;

    public function __construct(
        protected PlatformQueryService $platformQueryService,
        private readonly StatementElasticConnectionService $connectionService,
        private readonly StatementElasticAggregationService $aggregationService,
    ) {}

    private function client(): Client
    {
        return $this->connectionService->client();
    }

    private function indexName(): string
    {
        return $this->connectionService->statementIndexName();
    }

    /**
     * Get ElasticSearch indexing job statistics from the database
     */
    public function query(array $filters, array $options = [], $page = 0, $perPage = 50): array
    {
        $query = $this->buildQuery($filters);

        $results = $this->client()->search([
            'index' => $this->indexName(),
            'from' => $page * $perPage,
            'size' => $perPage,
            'track_total_hits' => true,
            'q' => $query,
            'sort' => 'id:desc',
        ])->asArray();

        $statement_ids = [];
        foreach ($results['hits']['hits'] as $result) {
            $statement_ids[] = $result['_id'];
        }

        $statement_ids = array_unique($statement_ids);

        return [
            'statements' => Statement::query()->whereIn('id', $statement_ids),
            'total' => $results['hits']['total']['value'] ?? 0,
        ];
    }

    public function buildQuery(array $filters): string
    {
        $queryAndParts = [];
        $query = '*';

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

        // dd($queryAndParts);

        // handle the date filters as needed.
        $created_at_filter = $this->applyCreatedAtFilter($filters);
        if ($created_at_filter !== '' && $created_at_filter !== '0') {
            $queryAndParts[] = $created_at_filter;
        }

        // if we have parts, then glue them together with AND
        if ($queryAndParts !== []) {
            $query = '('.implode(') AND (', $queryAndParts).')';
        }

        return $query;
    }

    private function applyCreatedAtFilter(array $filters): string
    {
        try {
            // Start but no end.
            if (($filters['created_at_start'] ?? false) && ! ($filters['created_at_end'] ?? false)) {
                $now = date('Y-m-d\TH:i:s');
                $start = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_start'].' 00:00:00');

                return 'created_at:['.$start->format('Y-m-d\TH:i:s').' TO '.$now.']';
            }

            // End but no start.
            if (($filters['created_at_end'] ?? false) && ! ($filters['created_at_start'] ?? false)) {
                $beginning = date('Y-m-d\TH:i:s', strtotime('2020-01-01'));
                $end = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_end'].' 23:59:59');

                return 'created_at:['.$beginning.' TO '.$end->format('Y-m-d\TH:i:s').']';
            }

            // both start and end.
            if (($filters['created_at_start'] ?? false) && ($filters['created_at_end'] ?? false)) {
                $start = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_start'].' 00:00:00');
                $end = Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_end'].' 23:59:59');

                return 'created_at:['.$start->format('Y-m-d\TH:i:s').' TO '.$end->format('Y-m-d\TH:i:s').']';
            }
        } catch (Exception) {
            // Most likely the date supplied for the start or the end was bad.
            return '';
        }

        // Normally we don't get here.
        return '';
    }

    private function applySFilter(string $filter_value): string
    {
        $filter_value = preg_replace("/[^a-zA-Z0-9\ \-\_]+/", '', $filter_value);
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
            'content_id_ean',
        ];

        $ors = [];
        foreach ($textfields as $textfield) {
            $ors[] = $textfield.':"'.$filter_value.'"';
        }

        if (config('app.env', '') !== 'testing') {

            return $filter_value;

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
            $ors[] = 'decision_visibility:'.$filter_value;
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
            $ors[] = 'decision_monetary:'.$filter_value;
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
            $ors[] = 'decision_provision:'.$filter_value;
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
            $ors[] = 'territorial_scope:'.$filter_value;
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
            $ors[] = 'decision_account:'.$filter_value;
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
            $ors[] = 'account_type:'.$filter_value;
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
            $ors[] = 'category_specification:'.$filter_value;
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
            $ors[] = 'decision_ground:'.$filter_value;
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
            $ors[] = 'category:'.$filter_value;
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
            $ors[] = 'content_type:'.$filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyContentLanguageFilter(array $filter_values): string
    {
        $ors = [];
        if (in_array('--noval--', $filter_values)) {
            $ors[] = '(-content_language:?*)';
        }
        $all_isos = array_keys(EuropeanLanguagesService::ALL_LANGUAGES);
        $filter_values = array_intersect($filter_values, $all_isos);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'content_language:"'.$filter_value.'"';
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
            $ors[] = 'automated_detection:'.($filter_value === Statement::AUTOMATED_DETECTION_YES ? 'true' : 'false');
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
            $ors[] = 'automated_decision:'.$filter_value;
        }

        return implode(' OR ', $ors);
    }

    private function applyPlatformIdFilter(array $filter_values): string
    {
        $ors = [];
        $platform_ids = $this->platformQueryService->getPlatformIds();
        $filter_values = array_filter($filter_values, 'is_scalar');
        $filter_values = array_intersect($platform_ids, $filter_values);
        foreach ($filter_values as $filter_value) {
            $ors[] = 'platform_id:'.$filter_value;
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
            $ors[] = 'source_type:'.$filter_value;
        }

        return implode(' OR ', $ors);
    }

    public function deleteStatementsForDate(Carbon $date): array
    {
        // Set to the very end of the day: 23:59:59.999
        $date->setTime(23, 59, 59, 999999); // 999 milliseconds in microseconds
        $timestamp = $date->getTimestampMs();

        return $this->client()->deleteByQuery([
            'index' => $this->indexName(),
            'body' => [
                'query' => [
                    'range' => [
                        'received_date' => [
                            'lte' => $timestamp,
                        ],
                    ],
                ],
            ],
            'wait_for_completion' => false,
        ])->asArray();
    }

    public function deleteStatementsBeforeDate(Carbon $cutoff, bool $waitForCompletion = false): array
    {
        $timestamp = $cutoff->copy()->startOfDay()->getTimestampMs();

        return $this->client()->deleteByQuery([
            'index' => $this->indexName(),
            'body' => [
                'query' => [
                    'range' => [
                        'received_date' => [
                            'lt' => $timestamp,
                        ],
                    ],
                ],
            ],
            'conflicts' => 'proceed',
            'wait_for_completion' => $waitForCompletion,
        ])->asArray();
    }

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
                if (! in_array($platform_id, $vlop_ids, true)) {
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
                if (($methods[Statement::METHOD_API] || $methods[Statement::METHOD_API_MULTI]) && ! in_array($platform_id, $vlop_ids, true)) {
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
                if ($methods[Statement::METHOD_FORM] && ! in_array($platform_id, $vlop_ids, true)) {
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
        return 'SELECT CAST(count(*) AS BIGINT) as count FROM '.$this->indexName();
    }

    public function buildWheres(array $conditions): string
    {
        if ($conditions !== []) {
            return ' WHERE '.implode(' AND ', $conditions);
        }

        return '';
    }

    public function extractCountQueryResult($result): int
    {
        return (int) ($result['rows'][0][0] ?? 0);
    }

    public function runSql(string $sql): array
    {
        if ($this->connectionService->isConfigured()) {
            return $this->client()->sql()->query([
                'body' => [
                    'query' => $sql,
                ],
                'format' => 'json',
            ])->asArray();
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
            'rows' => [
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
        return $this->extractCountQueryResult($this->runSql($this->startCountQuery().$this->buildWheres($conditions)));
    }

    public function highestId(): int
    {
        $sql = 'SELECT max(id) AS max_id FROM '.$this->indexName();
        $result = $this->runSql($sql);

        return (int) ($result['rows'][0][0] ?? 0);
    }

    public function grandTotal(): int
    {
        return Cache::remember('grand_total', self::ONE_DAY, fn () => $this->grandTotalNoCache());
    }

    public function grandTotalNoCache(): int
    {
        return $this->getCountQueryResult();
    }

    public function receivedDateCondition(Carbon $date): string
    {
        return "received_date = '".$date->format('Y-m-d')."'";
    }

    public function totalForDate(Carbon $date): int
    {
        return $this->getCountQueryResult([$this->receivedDateCondition($date)]);
    }

    public function totalForPlatformDate(Platform $platform, Carbon $date): int
    {
        return $this->getCountQueryResult([
            'platform_id = '.$platform->id,
            $this->receivedDateCondition($date),
        ]);
    }

    public function totalsForPlatformsDate(Carbon $date): array
    {
        $aggregates = $this->aggregationService->processDateAggregate($date, ['platform_id']);

        return $aggregates['aggregates'];
    }

    public function methodsByPlatformsDate(Carbon $date): array
    {
        $query = 'SELECT COUNT(*), method, platform_id FROM '.$this->indexName()." WHERE received_date = '".$date->format('Y-m-d')."' GROUP BY platform_id, method";

        return $this->extractMethodAggregateFromQuery($query);
    }

    public function methodsByPlatformAll(): array
    {
        return Cache::remember('methods_by_platform_all', self::ONE_HOUR, function () {
            $dsa_team_platform_id = Platform::dsaTeamPlatformId();
            $query = 'SELECT CAST(count(*) AS BIGINT), method, platform_id FROM '.$this->indexName().' WHERE platform_id <> '.$dsa_team_platform_id.' GROUP BY platform_id, method';

            return $this->extractMethodAggregateFromQuery($query);
        });

    }

    private function extractMethodAggregateFromQuery(string $query): array
    {

        $out = [];
        if ($this->connectionService->isConfigured()) {
            $results = $this->runSql($query);
            $rows = $results['rows'];
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
        return "received_date BETWEEN '".$start->format('Y-m-d')."' AND '".$end->format('Y-m-d')."'";
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

        $results = $this->aggregationService->processRangeAggregate($start, $end, ['received_date']);

        foreach ($results['aggregates'] as $aggregate) {
            $prepare[$aggregate['received_date']] = $aggregate['total'];
        }

        return array_map(static fn ($date, $total) => [
            'date' => $date,
            'total' => $total,
        ], array_keys($prepare), array_values($prepare));
    }

    public function topCategories(): array
    {
        return Cache::remember('top_categories', self::ONE_DAY, fn () => $this->topCategoriesNoCache());
    }

    public function topCategoriesNoCache(): array
    {
        $results = [];
        $categories = array_keys(Statement::STATEMENT_CATEGORIES);
        foreach ($categories as $category) {
            $results[] = [
                'value' => $category,
                'total' => $this->getCountQueryResult(["category = '".$category."'"]),
            ];
        }

        uasort($results, static fn ($a, $b) => ($a['total'] <=> $b['total']) * -1);

        return $results;
    }

    public function topDecisionVisibilities(): array
    {
        return Cache::remember('top_decisions_visibility', self::ONE_DAY, fn () => $this->topDecisionVisibilitiesNoCache());
    }

    public function topDecisionVisibilitiesNoCache(): array
    {
        $results = [];
        $decision_visibilities = array_keys(Statement::DECISION_VISIBILITIES);
        foreach ($decision_visibilities as $decision_visibility) {
            $results[] = [
                'value' => $decision_visibility,
                'total' => $this->getCountQueryResult(["decision_visibility_single = '".$decision_visibility."'"]),
            ];
        }

        uasort($results, static fn ($a, $b) => ($a['total'] <=> $b['total']) * -1);

        return $results;
    }

    public function fullyAutomatedDecisionPercentage(): int
    {
        return Cache::remember('automated_decisions_percentage', self::ONE_DAY, fn () => $this->fullyAutomatedDecisionPercentageNoCache());
    }

    public function fullyAutomatedDecisionPercentageNoCache(): int
    {
        $automated_decision_count = $this->getCountQueryResult(["automated_decision = 'AUTOMATED_DECISION_FULLY'"]);
        $total = $this->grandTotal();

        return round((($automated_decision_count / max(1, $total)) * 100));
    }

    public function uuidToId(string $uuid): int
    {
        $query = [
            'size' => 1,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'match_phrase' => [
                                'uuid' => $uuid,
                            ],
                        ],
                    ],
                ],
            ],
            '_source' => [
                'includes' => [
                    'id',
                ],
                'excludes' => [],
            ],
        ];

        $result = $this->client()->search([
            'index' => $this->indexName(),
            'body' => $query,
        ])->asArray();

        return $result['hits']['hits'][0]['_source']['id'] ?? 0;
    }

    public function PlatformIdPuidToId(int $platform_id, string $puid): int
    {
        return $this->PlatformIdPuidToIds($platform_id, $puid)[0] ?? 0;
    }

    public function PlatformIdPuidToIds(int $platform_id, string $puid): array
    {
        $puid = str_replace('=', '.', $puid);
        $query = [
            'size' => 1000,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'match_phrase' => [
                                'puid' => $puid,
                            ],
                        ],
                        [
                            'match' => [
                                'platform_id' => $platform_id,
                            ],
                        ],
                    ],
                ],
            ],
            '_source' => [
                'includes' => [
                    'id',
                ],
                'excludes' => [],
            ],
        ];

        $result = $this->client()->search([
            'index' => $this->indexName(),
            'body' => $query,
        ])->asArray();

        return array_map(static fn ($hit) => $hit['_id'], $result['hits']['hits'] ?? []);
    }
}
