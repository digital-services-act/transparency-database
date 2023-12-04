<?php

namespace App\Console\Commands;

use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use OpenSearch\Client;

class CreateStatementIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:create-index {index} {shards} {replicas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an Opensearch Statements Index';

    /**
     * Execute the console command.
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        /** @var Client $client */
        $client     = app(Client::class);
        $index = $this->argument('index');

        if ($client->indices()->exists(['index' => $index])) {
           $this->warn('Index with that name already exists!');
           return;
        }

        $properties = $statement_search_service->statementIndexProperties();

        $body = [
            'mappings' => $properties,
            'settings' => [
                'number_of_shards' => $this->argument('shards'),
                'number_of_replicas' => $this->argument('replicas')
            ]
        ];

        $client->indices()->create(['index' => $this->argument('index'), 'body' => $body]);
    }

}
