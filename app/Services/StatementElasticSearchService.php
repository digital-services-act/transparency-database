<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use stdClass;
use Throwable;

/**
 * @codeCoverageIgnore This whole service does many elasticsearch calls. Mocking the returns is not possible
 */
class StatementElasticSearchService
{
    private string $index_name = 'statement_index';

    private ?Client $client = null;

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

    public function __construct(protected PlatformQueryService $platformQueryService)
    {
        $this->client = $this->makeClient();
    }

    private function makeClient(): ?Client
    {
        $hosts = $this->configuredHosts();

        if ($hosts === []) {
            return null;
        }

        $builder = ClientBuilder::create()
            ->setHosts($hosts)
            ->setRetries((int) config('elasticsearch.retries', 2));

        $username = config('elasticsearch.basicAuthentication.username');
        $password = config('elasticsearch.basicAuthentication.password');

        if (is_string($username) && $username !== '' && is_string($password) && $password !== '') {
            $builder->setBasicAuthentication($username, $password);
        }

        return $builder->build();
    }

    public function client(): Client
    {
        if ($this->client === null) {
            throw new RuntimeException('Elasticsearch is not configured. Set ES_ADDON_HOST, ES_ADDON_USER, and ES_ADDON_PASSWORD.');
        }

        return $this->client;
    }

    private function rebuildClient(): void
    {
        $this->client = $this->makeClient();
    }

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }

    public static function hasConfiguredUris(): bool
    {
        return self::configuredUris() !== [];
    }

    private function configuredHosts(): array
    {
        return self::configuredUris();
    }

    private static function configuredUris(): array
    {
        $hosts = config('elasticsearch.uri', []);

        if (! is_array($hosts)) {
            $hosts = [$hosts];
        }

        return array_values(array_filter(array_map(static function ($host): ?string {
            if (! is_string($host)) {
                return null;
            }

            $host = trim($host);

            return $host === '' ? null : $host;
        }, $hosts)));
    }

    public function getIndexList(): array
    {
        $stats = $this->client()->indices()->stats()->asArray();

        return array_keys($stats['indices'] ?? []);
    }

    public function getIndexInfo(string $index): array
    {
        // Check if index exists
        if (! $this->client()->indices()->exists(['index' => $index])->asBool()) {
            throw new RuntimeException('Index does not exist');
        }

        // Get index stats
        $stats = $this->client()->indices()->stats()->asArray();
        $indices = $stats['indices'];

        if (! isset($indices[$index])) {
            throw new RuntimeException('Index is not in the indices stats, probably you used an alias?');
        }

        $index_stats = $indices[$index];

        // Get mapping
        $mapping = $this->client()->indices()->getMapping(['index' => $index])->asArray();

        // Get shards
        $shards = $this->client()->cat()->shards(['index' => $index, 'format' => 'json'])->asArray();

        // Get aliases
        $alias = $this->client()->indices()->getAlias(['index' => $index])->asArray();

        // Process fields from mapping
        $fields = [];
        foreach ($mapping[$index]['mappings']['properties'] as $field => $field_info) {
            $fields[] = [$field, $field_info['type']];
        }

        // Process shards (only primary shards)
        $shards_report = [];
        foreach ($shards as $shard) {
            if ($shard['prirep'] === 'p') {
                $shards_report[$shard['shard']] = [$shard['shard'], $shard['state'], $shard['docs'], $shard['store']];
            }
        }
        ksort($shards_report);

        // Process aliases
        $aliases = array_keys($alias[$index]['aliases']);
        $aliases_formatted = [];
        foreach ($aliases as $alias_name) {
            $aliases_formatted[] = ['alias' => $alias_name];
        }

        return [
            'uuid' => $index_stats['uuid'],
            'documents' => $index_stats['primaries']['docs']['count'],
            'size_bytes' => $index_stats['total']['store']['size_in_bytes'],
            'fields' => $fields,
            'shards' => array_values($shards_report),
            'aliases' => $aliases_formatted,
        ];
    }

    public function deleteIndex(string $index): array
    {
        // Check if index exists
        if (! $this->client()->indices()->exists(['index' => $index])->asBool()) {
            throw new RuntimeException('Index does not exist');
        }

        // Delete the index
        $response = $this->client()->indices()->delete(['index' => $index])->asArray();

        return [
            'index' => $index,
            'deleted' => true,
            'acknowledged' => $response['acknowledged'] ?? false,
        ];
    }

    public function getTasks(): array
    {
        $tasklist = $this->client()->tasks()->list()->asArray();

        $cancellable = [];
        $all_tasks = [];

        foreach ($tasklist['nodes'] as $node => $node_info) {
            foreach ($node_info['tasks'] as $task_id => $task_info) {
                $processed_task = [
                    'id' => $task_id,
                    'node' => $node,
                    'type' => $task_info['type'] ?? 'unknown',
                    'action' => $task_info['action'] ?? 'unknown',
                    'description' => $task_info['description'] ?? '',
                    'start_time' => $task_info['start_time_in_millis'] ?? 0,
                    'running_time' => $task_info['running_time_in_nanos'] ?? 0,
                    'cancellable' => $task_info['cancellable'] ?? false,
                ];

                $all_tasks[] = $processed_task;

                if ($task_info['cancellable']) {
                    $cancellable[] = $processed_task;
                }
            }
        }

        return [
            'total_tasks' => count($all_tasks),
            'cancellable_tasks' => count($cancellable),
            'cancellable' => $cancellable,
            'all_tasks' => $all_tasks,
        ];
    }

    public function createIndexAlias(string $index, string $alias): array
    {
        // Check if index exists
        if (! $this->client()->indices()->exists(['index' => $index])->asBool()) {
            throw new RuntimeException('Index does not exist');
        }

        // Check if alias already exists on this index
        if ($this->client()->indices()->existsAlias(['index' => $index, 'name' => $alias])->asBool()) {
            throw new RuntimeException('Alias already exists on this index');
        }

        // Create the alias
        $response = $this->client()->indices()->putAlias(['index' => $index, 'name' => $alias])->asArray();

        return [
            'index' => $index,
            'alias' => $alias,
            'created' => true,
            'acknowledged' => $response['acknowledged'] ?? false,
        ];
    }

    public function deleteIndexAlias(string $index, string $alias): array
    {
        // Check if index exists
        if (! $this->client()->indices()->exists(['index' => $index])->asBool()) {
            throw new RuntimeException('Index does not exist');
        }

        // Check if alias exists on this index
        if (! $this->client()->indices()->existsAlias(['index' => $index, 'name' => $alias])->asBool()) {
            throw new RuntimeException('Alias does not exist on this index');
        }

        // Delete the alias
        $response = $this->client()->indices()->deleteAlias(['index' => $index, 'name' => $alias])->asArray();

        return [
            'index' => $index,
            'alias' => $alias,
            'deleted' => true,
            'acknowledged' => $response['acknowledged'] ?? false,
        ];
    }

    public function getIndexSettings(string $index): array
    {
        // Check if index exists
        if (! $this->client()->indices()->exists(['index' => $index])->asBool()) {
            throw new RuntimeException('Index does not exist');
        }

        // Get index settings
        $response = $this->client()->indices()->getSettings(['index' => $index])->asArray();
        $settings = $response[$index]['settings'] ?? [];

        // Process settings into readable format
        $processedSettings = $this->processIndexSettings($settings);

        return [
            'index' => $index,
            'settings' => $processedSettings,
            'raw_settings' => $settings,
        ];
    }

    private function processIndexSettings(array $settings): array
    {
        $processed = [];

        // Index-level settings
        if (isset($settings['index'])) {
            $indexSettings = $settings['index'];

            // Basic index settings
            $processed['Basic'] = [
                ['Number of Shards', $indexSettings['number_of_shards'] ?? 'N/A'],
                ['Number of Replicas', $indexSettings['number_of_replicas'] ?? 'N/A'],
                ['Creation Date', isset($indexSettings['creation_date']) ? date('Y-m-d H:i:s', $indexSettings['creation_date'] / 1000) : 'N/A'],
                ['UUID', $indexSettings['uuid'] ?? 'N/A'],
                ['Version Created', $indexSettings['version']['created'] ?? 'N/A'],
            ];

            // Refresh settings
            if (isset($indexSettings['refresh_interval'])) {
                $processed['Refresh'] = [
                    ['Refresh Interval', $indexSettings['refresh_interval']],
                ];
            }

            // Analysis settings
            if (isset($indexSettings['analysis'])) {
                $analysisInfo = [];
                if (isset($indexSettings['analysis']['analyzer'])) {
                    $analysisInfo[] = ['Analyzers', count($indexSettings['analysis']['analyzer']).' configured'];
                }
                if (isset($indexSettings['analysis']['tokenizer'])) {
                    $analysisInfo[] = ['Tokenizers', count($indexSettings['analysis']['tokenizer']).' configured'];
                }
                if (isset($indexSettings['analysis']['filter'])) {
                    $analysisInfo[] = ['Filters', count($indexSettings['analysis']['filter']).' configured'];
                }
                if (! empty($analysisInfo)) {
                    $processed['Analysis'] = $analysisInfo;
                }
            }

            // Routing settings
            if (isset($indexSettings['routing'])) {
                $processed['Routing'] = [
                    ['Allocation Include', $indexSettings['routing']['allocation']['include']['_tier_preference'] ?? 'N/A'],
                ];
            }

            // Other important settings
            $otherSettings = [];
            if (isset($indexSettings['max_result_window'])) {
                $otherSettings[] = ['Max Result Window', number_format($indexSettings['max_result_window'])];
            }
            if (isset($indexSettings['max_inner_result_window'])) {
                $otherSettings[] = ['Max Inner Result Window', number_format($indexSettings['max_inner_result_window'])];
            }
            if (isset($indexSettings['max_rescore_window'])) {
                $otherSettings[] = ['Max Rescore Window', number_format($indexSettings['max_rescore_window'])];
            }
            if (! empty($otherSettings)) {
                $processed['Advanced'] = $otherSettings;
            }
        }

        return $processed;
    }

    public function updateIndexRefreshInterval(string $index, int $interval): array
    {
        // Check if index exists
        if (! $this->client()->indices()->exists(['index' => $index])->asBool()) {
            throw new RuntimeException('Index does not exist');
        }

        // Get current settings for comparison
        $currentResponse = $this->client()->indices()->getSettings(['index' => $index])->asArray();
        $currentInterval = $currentResponse[$index]['settings']['index']['refresh_interval'] ?? 'N/A';

        // Update refresh interval
        $response = $this->client()->indices()->putSettings([
            'index' => $index,
            'body' => [
                'refresh_interval' => $interval.'s',
            ],
        ])->asArray();

        return [
            'index' => $index,
            'previous_interval' => $currentInterval,
            'new_interval' => $interval.'s',
            'updated' => true,
            'acknowledged' => $response['acknowledged'] ?? false,
        ];
    }

    public function updateIndexBulkMode(
        string $index,
        bool $enabled,
        int $restoreReplicas = 2,
        string $restoreRefreshInterval = '1s',
        bool $asyncTranslog = true,
        bool $refreshAfterDisable = true,
    ): array {
        if (! $this->client()->indices()->exists(['index' => $index])->asBool()) {
            throw new RuntimeException('Index does not exist');
        }

        $currentResponse = $this->client()->indices()->getSettings(['index' => $index])->asArray();
        $currentSettings = $currentResponse[$index]['settings']['index'] ?? [];
        if ($currentSettings === [] && $currentResponse !== []) {
            $firstIndexResponse = reset($currentResponse);
            $currentSettings = $firstIndexResponse['settings']['index'] ?? [];
        }
        $previousSettings = $this->extractBulkModeSettings($currentSettings);

        $newSettings = $enabled ? [
            'number_of_replicas' => 0,
            'refresh_interval' => '-1',
            'translog' => [
                'durability' => $asyncTranslog ? 'async' : 'request',
            ],
        ] : [
            'number_of_replicas' => $restoreReplicas,
            'refresh_interval' => $restoreRefreshInterval,
            'translog' => [
                'durability' => 'request',
            ],
        ];

        $response = $this->client()->indices()->putSettings([
            'index' => $index,
            'body' => [
                'index' => $newSettings,
            ],
        ])->asArray();

        $refreshed = false;
        if (! $enabled && $refreshAfterDisable) {
            $this->client()->indices()->refresh(['index' => $index])->asArray();
            $refreshed = true;
        }

        return [
            'index' => $index,
            'enabled' => $enabled,
            'previous_settings' => $previousSettings,
            'new_settings' => $this->extractBulkModeSettings($newSettings),
            'updated' => true,
            'acknowledged' => $response['acknowledged'] ?? false,
            'refreshed' => $refreshed,
        ];
    }

    private function extractBulkModeSettings(array $settings): array
    {
        return [
            'number_of_replicas' => $settings['number_of_replicas'] ?? 'N/A',
            'refresh_interval' => $settings['refresh_interval'] ?? 'N/A',
            'translog.durability' => $settings['translog']['durability'] ?? 'request',
        ];
    }

    public function swapIndexAlias(string $fromIndex, string $toIndex, string $alias): array
    {
        // Check if both indices exist
        if (! $this->client()->indices()->exists(['index' => $fromIndex])->asBool()) {
            throw new RuntimeException('Source index does not exist');
        }

        if (! $this->client()->indices()->exists(['index' => $toIndex])->asBool()) {
            throw new RuntimeException('Target index does not exist');
        }

        // Check if alias exists on source index
        if (! $this->client()->indices()->existsAlias(['index' => $fromIndex, 'name' => $alias])->asBool()) {
            throw new RuntimeException('Alias does not exist on source index');
        }

        // Check if alias already exists on target index
        if ($this->client()->indices()->existsAlias(['index' => $toIndex, 'name' => $alias])->asBool()) {
            throw new RuntimeException('Alias already exists on target index');
        }

        // Perform atomic alias swap
        $body = [
            'actions' => [
                [
                    'remove' => [
                        'index' => $fromIndex,
                        'alias' => $alias,
                    ],
                ],
                [
                    'add' => [
                        'index' => $toIndex,
                        'alias' => $alias,
                    ],
                ],
            ],
        ];

        $response = $this->client()->indices()->updateAliases(['body' => $body])->asArray();

        return [
            'from_index' => $fromIndex,
            'to_index' => $toIndex,
            'alias' => $alias,
            'swapped' => true,
            'acknowledged' => $response['acknowledged'] ?? false,
        ];
    }

    public function removeDocumentFromIndex(string $index, int $documentId): array
    {
        // Check if index exists
        if (! $this->client()->indices()->exists(['index' => $index])->asBool()) {
            throw new RuntimeException('Index does not exist');
        }

        // Validate document ID
        if ($documentId <= 0) {
            throw new RuntimeException('Invalid document ID');
        }

        try {
            // Attempt to delete the document
            $response = $this->client()->delete([
                'index' => $index,
                'id' => $documentId,
            ])->asArray();

            return [
                'index' => $index,
                'document_id' => $documentId,
                'deleted' => true,
                'result' => $response['result'] ?? 'unknown',
                'version' => $response['_version'] ?? null,
            ];
        } catch (Exception $e) {
            // Document might not exist - this is often not a fatal error
            if (str_contains($e->getMessage(), 'not_found')) {
                throw new RuntimeException('Document not found in index');
            }
            throw new RuntimeException('Failed to delete document: '.$e->getMessage());
        }
    }

    public function cancelAllTasks(): array
    {
        // Get current cancellable tasks before cancelling
        $tasksInfo = $this->getTasks();
        $cancellableCount = $tasksInfo['cancellable_tasks'];

        // Cancel all cancellable tasks
        $response = $this->client()->tasks()->cancel()->asArray();

        return [
            'cancelled_tasks' => $cancellableCount,
            'acknowledged' => true,
            'response' => $response,
        ];
    }

    /**
     * Get ElasticSearch indexing job statistics from the database
     */
    public function query(array $filters, array $options = [], $page = 0, $perPage = 50): array
    {
        $query = $this->buildQuery($filters);

        $results = $this->client()->search([
            'index' => $this->index_name,
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

    public function indexStatement(Statement $statement): string
    {
        $doc = $statement->toSearchableArray();

        $response = $this->client()->index([
            'index' => $this->index_name,
            'id' => $statement->id,
            'body' => $doc,
            'require_alias' => true,
        ])->asArray();

        if (isset($response['result']) && in_array($response['result'], ['created', 'updated'], true)) {
            return $response['result'];
        }

        throw new RuntimeException('Elasticsearch index error');
    }

    public function bulkIndexStatements(Collection $statements): void
    {
        if ($statements->count() !== 0) {
            $docs = [];
            /** @var Statement $statement */
            foreach ($statements as $statement) {
                $docs[] = $statement->toSearchableArray();
            }

            // Call the bulk and make them searchable.
            $this->bulkSearchableArrays($docs);
        }
    }

    public function benchmarkBulkIndexStatements(Collection $statements): array
    {
        $transform = $this->measureIndexingStep(function () use ($statements): array {
            $docs = [];

            /** @var Statement $statement */
            foreach ($statements as $statement) {
                $docs[] = $statement->toSearchableArray();
            }

            return $docs;
        });

        return $this->benchmarkBulkIndexSearchableArrays($transform['value'], [
            'rows' => $statements->count(),
            'transform_ms' => $transform['ms'],
        ]);
    }

    public function bulkIndexRawStatementsForIdRange(
        int $min,
        int $max,
        bool $range = true,
        string $direction = 'asc',
    ): void {
        $this->bulkIndexRawStatementRows(
            $this->rawStatementRowsForIdRange($min, $max, $range, $direction),
        );
    }

    public function benchmarkBulkIndexRawStatementsForIdRange(
        int $min,
        int $max,
        bool $range = true,
        string $direction = 'asc',
    ): array {
        $fetch = $this->measureIndexingStep(
            fn (): SupportCollection => $this->rawStatementRowsForIdRange($min, $max, $range, $direction),
        );

        $benchmark = $this->benchmarkBulkIndexRawStatementRows($fetch['value']);
        $benchmark['fetch_ms'] = $fetch['ms'];
        $benchmark['total_ms'] += $fetch['ms'];

        return $benchmark;
    }

    public function bulkIndexRawStatementRows(SupportCollection $statements): void
    {
        if ($statements->count() !== 0) {
            $this->bulkSearchableArrays(
                $this->searchableArraysFromRawStatementRows($statements),
            );
        }
    }

    public function benchmarkBulkIndexRawStatementRows(SupportCollection $statements): array
    {
        $transform = $this->measureIndexingStep(
            fn (): array => $this->searchableArraysFromRawStatementRows($statements),
        );

        return $this->benchmarkBulkIndexSearchableArrays($transform['value'], [
            'rows' => $statements->count(),
            'transform_ms' => $transform['ms'],
        ]);
    }

    public function rawStatementRowsForIdRange(
        int $min,
        int $max,
        bool $range = true,
        string $direction = 'asc',
    ): SupportCollection {
        $direction = $this->normalizeSortDirection($direction);

        $query = $this->rawStatementRowsQuery();

        if ($range) {
            $query->whereIn('s.id', range($min, $max));
        } else {
            $query->whereBetween('s.id', [$min, $max]);
        }

        return $query
            ->orderBy('s.id', $direction)
            ->get();
    }

    public function rawStatementRowsForDate(Carbon $date, int $limit): SupportCollection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $startOfDay->copy()->addDay();

        return $this->rawStatementRowsQuery()
            ->where('s.created_at', '>=', $startOfDay)
            ->where('s.created_at', '<', $endOfDay)
            ->orderBy('s.id')
            ->limit($limit)
            ->get();
    }

    public function latestRawStatementRows(int $limit): SupportCollection
    {
        return $this->rawStatementRowsQuery()
            ->orderByDesc('s.id')
            ->limit($limit)
            ->get();
    }

    public function searchableArraysFromRawStatementRows(SupportCollection $statements): array
    {
        $docs = [];

        foreach ($statements as $statement) {
            $docs[] = $this->rawStatementRowToSearchableArray($statement);
        }

        return $docs;
    }

    public function rawStatementRowToSearchableArray(stdClass $statement): array
    {
        $decisionVisibility = $this->decodeRawArray($statement->decision_visibility);
        $contentType = $this->decodeRawArray($statement->content_type);

        return [
            'id' => (int) $statement->id,
            'decision_visibility' => $decisionVisibility,
            'decision_visibility_single' => implode('__', $decisionVisibility),
            'category_specification' => $this->decodeRawArray($statement->category_specification),
            'decision_visibility_other' => $statement->decision_visibility_other,
            'decision_monetary' => $statement->decision_monetary,
            'decision_monetary_other' => $statement->decision_monetary_other,
            'decision_provision' => $statement->decision_provision,
            'decision_account' => $statement->decision_account,
            'account_type' => $statement->account_type,
            'decision_ground' => $statement->decision_ground,
            'content_type' => $contentType,
            'content_type_single' => implode('__', $contentType),
            'content_type_other' => $statement->content_type_other,
            'content_language' => $statement->content_language,
            'illegal_content_legal_ground' => $statement->illegal_content_legal_ground,
            'illegal_content_explanation' => $statement->illegal_content_explanation,
            'incompatible_content_ground' => $statement->incompatible_content_ground,
            'incompatible_content_explanation' => $statement->incompatible_content_explanation,
            'source_type' => $statement->source_type,
            'source_identity' => $statement->source_identity,
            'decision_facts' => $statement->decision_facts,
            'automated_detection' => $statement->automated_detection === Statement::AUTOMATED_DETECTION_YES,
            'automated_decision' => $statement->automated_decision,
            'category' => $statement->category,
            'category_addition' => $this->decodeRawArray($statement->category_addition),
            'platform_id' => (int) $statement->platform_id,
            'platform_name' => $statement->platform_name ?? 'deleted-name-'.$statement->platform_id,
            'platform_uuid' => $statement->platform_uuid ?? 'deleted-uuid-'.$statement->platform_id,
            'content_date' => $this->jsonDate($statement->content_date),
            'application_date' => $this->jsonDate($statement->application_date),
            'created_at' => $this->jsonDate($statement->created_at),
            'received_date' => $this->receivedDate($statement->created_at),
            'uuid' => $statement->uuid,
            'puid' => $statement->puid,
            'territorial_scope' => $this->decodeRawArray($statement->territorial_scope),
            'method' => $statement->method,
            'content_id_ean' => $statement->content_id_ean,
        ];
    }

    public function buildBulkBodyFromSearchableArrays(array $docs): string
    {
        $bulk = [];

        foreach ($docs as $doc) {
            $bulk[] = json_encode([
                'index' => [
                    '_index' => $this->index_name,
                    '_id' => $doc['id'],
                ],
            ], JSON_THROW_ON_ERROR);
            $bulk[] = json_encode($doc, JSON_THROW_ON_ERROR);
        }

        return $bulk === [] ? '' : implode("\n", $bulk)."\n";
    }

    private function bulkSearchableArrays(array $docs): void
    {
        $body = $this->buildBulkBodyFromSearchableArrays($docs);

        if ($body === '') {
            return;
        }

        $this->bulkWithRetry($body);
    }

    private function benchmarkBulkIndexSearchableArrays(array $docs, array $metrics): array
    {
        $ndjson = $this->measureIndexingStep(
            fn (): string => $this->buildBulkBodyFromSearchableArrays($docs),
        );
        $body = $ndjson['value'];

        $elasticMs = 0.0;
        $bulk = [
            'attempts' => 0,
            'retries' => 0,
            'retry_sleep_ms' => 0,
        ];

        if ($body !== '') {
            $elastic = $this->measureIndexingStep(fn (): array => $this->bulkWithRetry($body));
            $elasticMs = $elastic['ms'];
            $bulk = $elastic['value'];
        }

        $metrics['ndjson_ms'] = $ndjson['ms'];
        $metrics['elastic_ms'] = $elasticMs;
        $metrics['elastic_attempts'] = $bulk['attempts'];
        $metrics['elastic_retries'] = $bulk['retries'];
        $metrics['elastic_retry_sleep_ms'] = $bulk['retry_sleep_ms'];
        $metrics['payload_bytes'] = strlen($body);
        $metrics['payload_mb'] = round(strlen($body) / 1024 / 1024, 4);
        $metrics['total_ms'] = ($metrics['transform_ms'] ?? 0.0) + $metrics['ndjson_ms'] + $metrics['elastic_ms'];

        return $metrics;
    }

    private function bulkWithRetry(string $body): array
    {
        $delays = $this->bulkRetryDelaysMs();
        $maxAttempts = count($delays) + 1;
        $retrySleepMs = 0;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $this->client()->bulk([
                    'require_alias' => true,
                    'body' => $body,
                ]);

                return [
                    'attempts' => $attempt,
                    'retries' => $attempt - 1,
                    'retry_sleep_ms' => $retrySleepMs,
                ];
            } catch (Throwable $exception) {
                if (! $this->shouldRetryBulkException($exception) || $attempt >= $maxAttempts) {
                    throw $exception;
                }

                if ($exception instanceof NoNodeAvailableException) {
                    $this->rebuildClient();
                }

                $delayMs = $this->jitteredBulkRetryDelayMs($delays[$attempt - 1]);
                $retrySleepMs += $delayMs;

                Log::warning('Elasticsearch bulk retry', [
                    'attempt' => $attempt,
                    'next_attempt' => $attempt + 1,
                    'max_attempts' => $maxAttempts,
                    'delay_ms' => $delayMs,
                    'exception' => get_class($exception),
                    'status' => $this->bulkExceptionStatus($exception),
                    'message' => $exception->getMessage(),
                    'payload_mb' => round(strlen($body) / 1024 / 1024, 4),
                ]);

                usleep($delayMs * 1000);
            }
        }

        throw new RuntimeException('Elasticsearch bulk retry loop exited unexpectedly.');
    }

    private function shouldRetryBulkException(Throwable $exception): bool
    {
        if ($exception instanceof NoNodeAvailableException || $exception instanceof ServerResponseException) {
            return true;
        }

        if ($exception instanceof ClientResponseException) {
            return in_array($this->bulkExceptionStatus($exception), [408, 429], true);
        }

        return false;
    }

    private function bulkExceptionStatus(Throwable $exception): ?int
    {
        if (! method_exists($exception, 'getResponse')) {
            return null;
        }

        try {
            return $exception->getResponse()->getStatusCode();
        } catch (Throwable) {
            return null;
        }
    }

    private function bulkRetryDelaysMs(): array
    {
        $delays = config('elasticsearch.bulk_retry_delays_ms', [250, 750, 1500, 3000]);

        if (! is_array($delays)) {
            $delays = [$delays];
        }

        return array_values(array_filter(
            array_map(static fn ($delay): int => max(0, (int) $delay), $delays),
            static fn (int $delay): bool => $delay > 0,
        ));
    }

    private function jitteredBulkRetryDelayMs(int $delayMs): int
    {
        return max(1, $delayMs + random_int(0, max(1, (int) floor($delayMs / 5))));
    }

    private function measureIndexingStep(callable $callback): array
    {
        $start = hrtime(true);
        $value = $callback();

        return [
            'value' => $value,
            'ms' => round((hrtime(true) - $start) / 1_000_000, 3),
        ];
    }

    private function rawStatementRowsQuery(): QueryBuilder
    {
        return DB::table('statements_beta as s')
            ->leftJoin('platforms as p', function ($join): void {
                $join->on('p.id', '=', 's.platform_id')
                    ->whereNull('p.deleted_at');
            })
            ->whereNull('s.deleted_at')
            ->select($this->rawStatementSelectColumns());
    }

    private function rawStatementSelectColumns(): array
    {
        return [
            's.id',
            's.uuid',
            's.decision_visibility',
            's.decision_visibility_other',
            's.decision_monetary',
            's.decision_monetary_other',
            's.decision_provision',
            's.decision_account',
            's.account_type',
            's.decision_ground',
            's.content_type',
            's.content_type_other',
            's.content_language',
            's.illegal_content_legal_ground',
            's.illegal_content_explanation',
            's.incompatible_content_ground',
            's.incompatible_content_explanation',
            's.source_type',
            's.source_identity',
            's.decision_facts',
            's.automated_detection',
            's.automated_decision',
            's.category',
            's.category_addition',
            's.category_specification',
            's.platform_id',
            'p.name as platform_name',
            'p.uuid as platform_uuid',
            's.content_date',
            's.application_date',
            's.created_at',
            's.puid',
            's.territorial_scope',
            's.method',
            's.content_id_ean',
        ];
    }

    private function decodeRawArray(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        $decoded = array_unique($decoded);
        sort($decoded);

        return array_values($decoded);
    }

    private function jsonDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->toJSON();
    }

    private function receivedDate(?string $createdAt): ?string
    {
        if ($createdAt === null || $createdAt === '') {
            return null;
        }

        return Carbon::parse($createdAt)->startOfDay()->toJSON();
    }

    private function normalizeSortDirection(string $direction): string
    {
        $direction = strtolower($direction);

        if (! in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Raw statement sort direction must be asc or desc.');
        }

        return $direction;
    }

    public function deleteStatementsForDate(Carbon $date): array
    {
        // Set to the very end of the day: 23:59:59.999
        $date->setTime(23, 59, 59, 999999); // 999 milliseconds in microseconds
        $timestamp = $date->getTimestampMs();

        return $this->client()->deleteByQuery([
            'index' => $this->index_name,
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
            'index' => $this->index_name,
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
        return 'SELECT CAST(count(*) AS BIGINT) as count FROM '.$this->index_name;
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
        if ($this->client !== null) {
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
        $sql = 'SELECT max(id) AS max_id FROM '.$this->index_name;
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
        $aggregates = $this->processDateAggregate($date, ['platform_id']);

        return $aggregates['aggregates'];
    }

    public function methodsByPlatformsDate(Carbon $date): array
    {
        $query = 'SELECT COUNT(*), method, platform_id FROM '.$this->index_name." WHERE received_date = '".$date->format('Y-m-d')."' GROUP BY platform_id, method";

        return $this->extractMethodAggregateFromQuery($query);
    }

    public function methodsByPlatformAll(): array
    {
        return Cache::remember('methods_by_platform_all', self::ONE_HOUR, function () {
            $dsa_team_platform_id = Platform::dsaTeamPlatformId();
            $query = 'SELECT CAST(count(*) AS BIGINT), method, platform_id FROM '.$this->index_name.' WHERE platform_id <> '.$dsa_team_platform_id.' GROUP BY platform_id, method';

            return $this->extractMethodAggregateFromQuery($query);
        });

    }

    private function extractMethodAggregateFromQuery(string $query): array
    {

        $out = [];
        if ($this->client !== null) {
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

        $results = $this->processRangeAggregate($start, $end, ['received_date']);

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

    public function pushESAKey($key): void
    {
        $keys = Cache::get('esa_cache', []);
        $keys[] = $key;
        Cache::forever('esa_cache', array_unique($keys));
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
            'index' => $this->index_name,
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
            'index' => $this->index_name,
            'body' => $query,
        ])->asArray();

        return array_map(static fn ($hit) => $hit['_id'], $result['hits']['hits'] ?? []);
    }

    public function clearESACache(): void
    {
        $keys = Cache::get('esa_cache', []);
        foreach ($keys as $key) {
            Cache::delete($key);
        }

        Cache::delete('esa_cache');
    }

    public function processRangeAggregate(Carbon $start, Carbon $end, array $attributes, bool $caching = true): array
    {
        $timestart = microtime(true);

        $this->sanitizeAggregateAttributes($attributes);
        $key = 'esar__'.$start->format('Y-m-d').'__'.$end->format('Y-m-d').'__'.implode('__', $attributes);

        if (! $caching) {
            Cache::delete($key);
        }

        $cache = 'hit';
        $results = Cache::rememberForever($key, function () use ($start, $end, $attributes, $key, &$cache) {
            $query = $this->aggregateQueryRange($start, $end, $attributes);
            $cache = 'miss';
            $this->pushESAKey($key);

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
        $key = 'esad__'.$start->format('Y-m-d').'__'.$end->format('Y-m-d').'__'.implode('__', $attributes);

        if (! $caching) {
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
            $this->pushESAKey($key);

            return $days;
        });

        $total = array_sum(array_map(static fn ($day) => $day['total'], $days));

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
        $key = 'esa__'.$date->format('Y-m-d').'__'.implode('__', $attributes);

        if ($date > Carbon::yesterday()) {
            throw new RuntimeException('aggregates must done on dates in the past');
        }

        if (! $caching) {
            Cache::delete($key);
        }

        $cache = 'hit';
        $results = Cache::rememberForever($key, function () use ($date, $attributes, $key, &$cache) {
            $query = $this->aggregateQuerySingleDate($date, $attributes);
            $cache = 'miss';
            $this->pushESAKey($key);

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
        $query_string = <<<'JSON'
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
        $query_string = <<<'JSON'
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
        $source = new stdClass;
        $source->$attribute = new stdClass;
        $source->$attribute->terms = new stdClass;
        $source->$attribute->terms->field = $attribute;
        $source->$attribute->terms->missing_bucket = true;
        $source->$attribute->terms->missing_order = 'first';
        $source->$attribute->terms->order = 'asc';

        return $source;
    }

    public function processAggregateQuery(stdClass $query): array
    {
        $result = $this->client()->search([
            'index' => $this->index_name,
            'body' => $query,
        ])->asArray();
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
            $item['permutation'] = implode(',', array_map(static fn ($key, $value) => $key.':'.$value, array_keys($attributes), array_values($attributes)));

            // add the platform name on at the end if we need to.
            if (isset($item['platform_id'])) {
                // yes we will need it.
                $item['platform_name'] = $platforms[$item['platform_id']] ?? '';
            }

            $item['total'] = $bucket['doc_count'];
            $total += $bucket['doc_count'];
            $total_aggregates++;
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

    public function createStatementIndex(): void
    {

        $index = $this->index_name;
        $shards = 64;
        $replicas = 2;

        $properties = $this->statementIndexProperties();

        $body = [
            'mappings' => $properties,
            'settings' => [
                'number_of_shards' => $shards,
                'number_of_replicas' => $replicas,
            ],
        ];

        $this->client()->indices()->create(['index' => $index, 'body' => $body]);
    }

    public function deleteStatementIndex(): void
    {
        $index = $this->index_name;

        if ($this->client()->indices()->exists(['index' => $index])) {
            $this->client()->indices()->delete(['index' => $index]);
        }
    }

    public function statementIndexProperties(): array
    {
        return [
            'properties' => [
                'automated_decision' => [
                    'type' => 'keyword',
                ],
                'automated_detection' => [
                    'type' => 'boolean',
                ],
                'category' => [
                    'type' => 'keyword',
                ],
                'category_specification' => [
                    'type' => 'text',
                ],
                'content_type' => [
                    'type' => 'text',
                ],
                'content_type_single' => [
                    'type' => 'keyword',
                ],
                'content_type_other' => [
                    'type' => 'text',
                ],
                'content_language' => [
                    'type' => 'keyword',
                ],
                'created_at' => [
                    'type' => 'date',
                ],
                'received_date' => [
                    'type' => 'date',
                ],
                'content_date' => [
                    'type' => 'date',
                ],
                'application_date' => [
                    'type' => 'date',
                ],
                'decision_account' => [
                    'type' => 'keyword',
                ],
                'account_type' => [
                    'type' => 'keyword',
                ],
                'decision_facts' => [
                    'type' => 'text',
                ],
                'decision_ground' => [
                    'type' => 'keyword',
                ],
                'decision_monetary' => [
                    'type' => 'keyword',
                ],
                'decision_provision' => [
                    'type' => 'keyword',
                ],
                'decision_visibility' => [
                    'type' => 'text',
                ],
                'decision_visibility_single' => [
                    'type' => 'keyword',
                ],
                'id' => [
                    'type' => 'long',
                ],
                'illegal_content_explanation' => [
                    'type' => 'text',
                ],
                'illegal_content_legal_ground' => [
                    'type' => 'text',
                ],
                'incompatible_content_explanation' => [
                    'type' => 'text',
                ],
                'incompatible_content_ground' => [
                    'type' => 'text',
                ],
                'platform_id' => [
                    'type' => 'long',
                ],
                'platform_name' => [
                    'type' => 'text',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword',
                            'ignore_above' => 256,
                        ],
                    ],
                ],
                'platform_uuid' => [
                    'type' => 'text',
                ],
                'source_identity' => [
                    'type' => 'text',
                ],
                'source_type' => [
                    'type' => 'keyword',
                ],
                'url' => [
                    'type' => 'text',
                ],
                'uuid' => [
                    'type' => 'text',
                ],
                'puid' => [
                    'type' => 'text',
                ],
                'decision_visibility_other' => [
                    'type' => 'text',
                ],
                'decision_monetary_other' => [
                    'type' => 'text',
                ],
                'territorial_scope' => [
                    'type' => 'text',
                ],
                'method' => [
                    'type' => 'keyword',
                ],
                'content_id_ean' => [
                    'type' => 'long',
                ],
            ],
        ];
    }
}
