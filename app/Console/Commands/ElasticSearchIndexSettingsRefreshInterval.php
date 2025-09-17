<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

class ElasticSearchIndexSettingsRefreshInterval extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-settings-refresh-interval {index} {interval}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the refresh interval for an Elasticsearch index.';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        $index = $this->argument('index');
        $interval = $this->intifyArgument('interval');

        try {
            $result = $elasticSearchService->updateIndexRefreshInterval($index, $interval);

            if ($result['acknowledged']) {
                $this->info("Refresh interval for index '{$result['index']}' has been updated.");
                $this->line("Previous interval: {$result['previous_interval']}");
                $this->line("New interval: {$result['new_interval']}");
            } else {
                $this->warn('Refresh interval update was not acknowledged by Elasticsearch.');
            }
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'does not exist')) {
                $this->error("Index '{$index}' does not exist.");
            } else {
                $this->error("Failed to update refresh interval for index '{$index}': ".$e->getMessage());
            }
        }
    }
}
