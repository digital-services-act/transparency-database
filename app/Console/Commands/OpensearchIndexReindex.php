<?php

namespace App\Console\Commands;

use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use OpenSearch\Client;

class OpensearchIndexReindex extends Command
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
    protected $description = 'Reindex an index into another Opensearch Index';

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
            'body' => [
                'conflicts' => "proceed",
                'source' => [
                    'index' => $index
                ],
                'dest' => [
                    'index' => $target,
                    'op_type' => 'create'
                ]
            ]
        ]);

        $this->info('Results');
        $this->table(['Time', 'Total', 'Updated', 'Created', 'Deleted'], [
            [
                $result['took'],
                $result['total'],
                $result['updated'],
                $result['created'],
                $result['deleted']
            ]
        ]);
    }
}
