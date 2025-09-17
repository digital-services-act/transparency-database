<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

class ElasticSearchIndexSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-settings {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get settings information about an Elasticsearch index.';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        $index = $this->argument('index');

        try {
            $settingsInfo = $elasticSearchService->getIndexSettings($index);

            $this->info("Settings for index: {$settingsInfo['index']}");
            $this->line(str_repeat('=', 50));

            foreach ($settingsInfo['settings'] as $category => $settings) {
                $this->newLine();
                $this->info("{$category} Settings:");
                $this->table(['Setting', 'Value'], $settings);
            }

            if (empty($settingsInfo['settings'])) {
                $this->warn('No processed settings found. Index may have minimal configuration.');
            }

        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'does not exist')) {
                $this->error("Index '{$index}' does not exist.");
            } else {
                $this->error("Failed to retrieve settings for index '{$index}': ".$e->getMessage());
            }
        }
    }
}
