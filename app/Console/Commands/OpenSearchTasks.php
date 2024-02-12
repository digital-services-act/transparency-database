<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;
use Symfony\Component\VarDumper\VarDumper;

class OpenSearchTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get some info about the open search tasks if they can be cancelled.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client = app(Client::class);

        $tasklist = $client->tasks()->tasksList();
        $cancellable = [];
        foreach ($tasklist['nodes'] as $node => $node_info) {
            foreach ($node_info['tasks'] as $ask => $task_info) {
                if ($task_info['cancellable']) {
                    $cancellable[] = $task_info;
                }
            }
        }

        VarDumper::dump($cancellable);
    }
}
