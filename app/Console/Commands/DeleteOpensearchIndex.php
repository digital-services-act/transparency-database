<?php

namespace App\Console\Commands;

use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use OpenSearch\Client;

class DeleteOpensearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:delete-index {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an Opensearch Statements Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client     = app(Client::class);
        $index = $this->argument('index');

        if ($client->indices()->exists(['index' => $index])) {
            $client->indices()->delete(['index' => $index]);
        }
    }
}
