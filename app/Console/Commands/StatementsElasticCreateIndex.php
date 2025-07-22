<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class StatementsElasticCreateIndex extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:elastic-create-index {index} {shards} {replicas=2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an Elasticsearch Statements Index';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $statement_search_service): void
    {
        /** @var Client $client */
        $client     = app(StatementElasticSearchService::class)->client();
        $index = $this->argument('index');
        $shards = $this->intifyArgument('shards');
        $replicas = $this->intifyArgument('replicas');

        if ($client->indices()->exists(['index' => $index])->asBool()) {
           $this->warn('Index with that name already exists!');
           return;
        }

        $properties = $statement_search_service->statementIndexProperties();

        $body = [
            'mappings' => $properties,
            'settings' => [
                'number_of_shards' => $shards,
                'number_of_replicas' => $replicas
            ]
        ];

        $client->indices()->create(['index' => $this->argument('index'), 'body' => $body]);
    }

}
