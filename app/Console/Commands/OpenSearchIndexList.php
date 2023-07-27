<?php

namespace App\Console\Commands;

use App\Jobs\SpamStatementCreation;
use App\Jobs\StatementCreation;
use App\Models\Statement;
use Illuminate\Console\Command;
use Illuminate\Console\View\Components\Info;
use OpenSearch\Client;

class OpenSearchIndexList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index_list';

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
        if (env('SCOUT_DRIVER', '') !== 'opensearch')
        {
            $this->error('opensearch is not the SCOUT_DRIVER');
            return;
        }

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
