<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

class ElasticSearchIndexAliasDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-alias-delete {index} {alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an alias from an Elasticsearch Index';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        $index = $this->argument('index');
        $alias = $this->argument('alias');

        try {
            $result = $elasticSearchService->deleteIndexAlias($index, $alias);

            if ($result['acknowledged']) {
                $this->info("Alias '{$result['alias']}' has been successfully deleted from index '{$result['index']}'.");
            } else {
                $this->warn("Alias '{$result['alias']}' deletion was not acknowledged by Elasticsearch.");
            }
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Index does not exist')) {
                $this->error("Index '{$index}' does not exist.");
            } elseif (str_contains($e->getMessage(), 'does not exist on this index')) {
                $this->warn("Alias '{$alias}' does not exist on index '{$index}'.");
            } else {
                $this->error("Failed to delete alias '{$alias}' from index '{$index}': ".$e->getMessage());
            }
        }
    }
}
