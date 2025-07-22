<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
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
    public function handle(): void
    {
        /** @var Client $client */
        $client  = app(StatementElasticSearchService::class)->client();

        $indexes = array_keys($client->indices()->stats()->asArray()['indices']);
        $rows = [];
        foreach ($indexes as $index)
        {
            $rows[] = [$index];
        }

        $this->table(['Indexes'], $rows);
    }
}
