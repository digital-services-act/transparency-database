<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use OpenSearch\Client;

/**
 * @codeCoverageIgnore
 */
class OpenSearchIndexReindex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index-reindex {index} {target}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex an index into another OpenSearch Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client     = app(Client::class);
        $index = $this->argument('index');
        $target = $this->argument('target');

        if (!$client->indices()->exists(['index' => $index])) {
            $this->warn('Source index does not exist!');
            return;
        }

        if (!$client->indices()->exists(['index' => $target])) {
            $this->warn('Target index does not exist!');
            return;
        }

        $result = $client->reindex([
            'wait_for_completion' => false,
            'body' => [
                'conflicts' => "proceed",
                'source'    => [
                    'index' => $index,
                ],
                'dest'      => [
                    'index'   => $target,
                    'op_type' => 'create'
                ]
            ]
        ]);
    }
}
