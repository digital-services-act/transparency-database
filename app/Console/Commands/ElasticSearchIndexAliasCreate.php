<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

class ElasticSearchIndexAliasCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-alias-create {index} {alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an alias on an Elasticsearch Index';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        $index = $this->argument('index');
        $alias = $this->argument('alias');

        try {
            $result = $elasticSearchService->createIndexAlias($index, $alias);

            if ($result['acknowledged']) {
                $this->info("Alias '{$result['alias']}' has been successfully created on index '{$result['index']}'.");
            } else {
                $this->warn("Alias '{$result['alias']}' creation was not acknowledged by Elasticsearch.");
            }
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'does not exist')) {
                $this->error("Index '{$index}' does not exist.");
            } elseif (str_contains($e->getMessage(), 'already exists')) {
                $this->warn("Alias '{$alias}' already exists on index '{$index}'.");
            } else {
                $this->error("Failed to create alias '{$alias}' on index '{$index}': ".$e->getMessage());
            }
        }
    }
}
