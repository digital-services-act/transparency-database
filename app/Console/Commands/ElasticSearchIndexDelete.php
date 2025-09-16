<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

class ElasticSearchIndexDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-delete {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an Elasticsearch Index';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        $index = $this->argument('index');

        try {
            $result = $elasticSearchService->deleteIndex($index);

            if ($result['acknowledged']) {
                $this->info("Index '{$result['index']}' has been successfully deleted.");
            } else {
                $this->warn("Index '{$result['index']}' deletion was not acknowledged by Elasticsearch.");
            }
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'does not exist')) {
                $this->error("Index '{$index}' does not exist.");
            } else {
                $this->error("Failed to delete index '{$index}': ".$e->getMessage());
            }
        }
    }
}
