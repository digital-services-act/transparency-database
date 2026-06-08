<?php

namespace App\Console\Commands;

use App\Services\StatementElasticToolsService;
use Illuminate\Console\Command;

class ElasticSearchIndexList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get some info about the elasticsearch.';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticToolsService $elasticSearchService): void
    {
        $indexes = $elasticSearchService->getIndexList();

        $rows = [];
        foreach ($indexes as $index) {
            $rows[] = [$index];
        }

        $this->table(['Indexes'], $rows);
    }
}
