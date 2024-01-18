<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;

class OpenSearchTasksCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:tasks-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel any tasks that can be cancelled.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client = app(Client::class);

        $client->tasks()->cancel();
    }
}
