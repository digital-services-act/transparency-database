<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

class ElasticSearchIndexAliasSwap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-alias-swap {index} {target} {alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Swap an alias from one Elasticsearch index to another index atomically';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        $fromIndex = $this->argument('index');
        $toIndex = $this->argument('target');
        $alias = $this->argument('alias');

        try {
            $result = $elasticSearchService->swapIndexAlias($fromIndex, $toIndex, $alias);

            if ($result['acknowledged']) {
                $this->info("Alias '{$result['alias']}' has been successfully swapped.");
                $this->line("From index: {$result['from_index']}");
                $this->line("To index: {$result['to_index']}");
            } else {
                $this->warn('Alias swap was not acknowledged by Elasticsearch.');
            }
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Source index does not exist')) {
                $this->error("Source index '{$fromIndex}' does not exist.");
            } elseif (str_contains($e->getMessage(), 'Target index does not exist')) {
                $this->error("Target index '{$toIndex}' does not exist.");
            } elseif (str_contains($e->getMessage(), 'does not exist on source')) {
                $this->warn("Alias '{$alias}' does not exist on source index '{$fromIndex}'.");
            } elseif (str_contains($e->getMessage(), 'already exists on target')) {
                $this->warn("Alias '{$alias}' already exists on target index '{$toIndex}'.");
            } else {
                $this->error("Failed to swap alias '{$alias}': ".$e->getMessage());
            }
        }
    }
}
