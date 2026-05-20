<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;
use RuntimeException;

class ElasticSearchIndexBulkMode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-bulk-mode
        {index : Elasticsearch index or write alias to update}
        {mode=on : Enable or disable bulk indexing mode. Allowed values: on, off}
        {--replicas=2 : Number of replicas to restore when disabling bulk mode}
        {--refresh-interval=1s : Refresh interval to restore when disabling bulk mode}
        {--sync-translog : Keep request translog durability when enabling bulk mode}
        {--skip-refresh : Do not manually refresh the index after disabling bulk mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle Elasticsearch index settings optimized for bulk indexing.';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): int
    {
        $index = (string) $this->argument('index');
        $mode = strtolower((string) $this->argument('mode'));

        if (! in_array($mode, ['on', 'off'], true)) {
            $this->error("Mode must be 'on' or 'off'.");

            return self::FAILURE;
        }

        $restoreReplicas = (int) $this->option('replicas');
        if ($restoreReplicas < 0) {
            $this->error('The --replicas option must be 0 or greater.');

            return self::FAILURE;
        }

        $restoreRefreshInterval = trim((string) $this->option('refresh-interval'));
        if ($restoreRefreshInterval === '') {
            $this->error('The --refresh-interval option cannot be empty.');

            return self::FAILURE;
        }

        $enabled = $mode === 'on';

        try {
            $result = $elasticSearchService->updateIndexBulkMode(
                $index,
                $enabled,
                $restoreReplicas,
                $restoreRefreshInterval,
                ! (bool) $this->option('sync-translog'),
                ! (bool) $this->option('skip-refresh'),
            );

            if (! $result['acknowledged']) {
                $this->warn('Bulk indexing mode update was not acknowledged by Elasticsearch.');

                return self::FAILURE;
            }

            $this->info(sprintf(
                'Bulk indexing mode %s for index "%s".',
                $enabled ? 'enabled' : 'disabled',
                $result['index'],
            ));

            $this->table(['Setting', 'Previous', 'New'], $this->settingsRows(
                $result['previous_settings'],
                $result['new_settings'],
            ));

            if ($enabled && $result['new_settings']['translog.durability'] === 'async') {
                $this->warn('Translog durability is async. Recent acknowledged writes may be lost if the node crashes during this run.');
            }

            if (! $enabled && $result['refreshed']) {
                $this->info('Index refreshed after restoring normal indexing settings.');
            }

            return self::SUCCESS;
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'does not exist')) {
                $this->error("Index '{$index}' does not exist.");
            } else {
                $this->error("Failed to update bulk indexing mode for index '{$index}': ".$e->getMessage());
            }

            return self::FAILURE;
        }
    }

    private function settingsRows(array $previousSettings, array $newSettings): array
    {
        return array_map(static fn (string $setting): array => [
            $setting,
            (string) ($previousSettings[$setting] ?? 'N/A'),
            (string) ($newSettings[$setting] ?? 'N/A'),
        ], [
            'number_of_replicas',
            'refresh_interval',
            'translog.durability',
        ]);
    }
}
