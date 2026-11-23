<?php

namespace App\Services;

use App\Models\Statement;
use Elastic\Elasticsearch\Client;
use Exception;
use Illuminate\Support\Carbon;
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

    public function __construct(
        protected PlatformQueryService $platformQueryService,
        private readonly StatementElasticConnectionService $connectionService,
    ) {}

    private function client(): Client
    {
        return $this->connectionService->client();
    }

    private function indexName(): string
    {
        return $this->connectionService->statementIndexName();
    }

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
        return preg_replace("/[^a-zA-Z0-9\ \-\_]+/", '', $filter_value);
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
