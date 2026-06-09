<?php

namespace App\Services;

use Elastic\Elasticsearch\Client;
use Exception;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * @codeCoverageIgnore This service does Elasticsearch index and cluster administration calls.
 */
class StatementElasticToolsService
{
    public function __construct(
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

    public function createStatementIndex(): void
    {

        $index = $this->indexName();
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
        $index = $this->indexName();

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
