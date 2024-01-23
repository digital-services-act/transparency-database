<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;

class OpenSearchIndexList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get some info about the open search.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client  = app(Client::class);

        $indexes = array_keys($client->indices()->stats()['indices']);
        $rows = [];
        foreach ($indexes as $index)
        {
            $rows[] = [$index];
        }
        $this->table(['Indexes'], $rows);
    }
}
