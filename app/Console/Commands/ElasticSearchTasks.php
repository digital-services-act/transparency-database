<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @codeCoverageIgnore
 */
class ElasticSearchTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get some info about the elasticsearch tasks if they can be cancelled.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client = app(StatementElasticSearchService::class)->client();

        $tasklist = $client->tasks()->list()->asArray();
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
