<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use OpenSearch\Client;

class OpensearchIndexReindex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index-reindex {index} {target} {lowest=default';

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

        $lowest = $this->argument('lowest') === 'default' ? $this->lowestId($client, $index) : (int)$this->argument('lowest');
        $current = $this->highestId($client, $index);

        $chunk = 1000000;

        while ($current >= $lowest) {
            $this->reindexChunk($client, $index, $target, $current, $current - $chunk);
            $current -= $chunk;
        }

    }


    private function highestId(Client $client, $index): int
    {
        $result = $client->sql()->query([
            'query' => 'SELECT max(id) AS max_id FROM ' . $index,
        ]);
        return $result['datarows'][0][0];
    }

    private function lowestId(Client $client, $index): int
    {
        $result = $client->sql()->query([
            'query' => 'SELECT min(id) AS min_id FROM ' . $index,
        ]);
        return $result['datarows'][0][0];
    }


    private function reindexChunk(Client $client, string $index, string $target, int $start, int $end): void
    {
        $this->info('Chunk: ' . $start . ' :: ' . $end);
        $complete = false;
        $attempts = 1;

        while (!$complete) {
            try {
                $result = $client->reindex([
                    'wait_for_completion' => false,
                    'body' => [
                        'conflicts' => "proceed",
                        'source'    => [
                            'index' => $index,
                            'query' => [
                                "bool" => [
                                    "filter"               => [
                                        [
                                            "range" => [
                                                "id" => [
                                                    "from"          => $end,
                                                    "to"            => $start,
                                                    "include_lower" => false,
                                                    "include_upper" => true,
                                                    "boost"         => 1.0
                                                ]
                                            ]
                                        ]
                                    ],
                                    "adjust_pure_negative" => true,
                                    "boost"                => 1.0
                                ]
                            ]
                        ],
                        'dest'      => [
                            'index'   => $target,
                            'op_type' => 'create'
                        ]
                    ]
                ]);

                $this->info('done');

                $complete = true;
            } catch (Exception $e) {
                $attempts++;
                $this->info('Exception: ' . $e->getMessage());
                $this->info('Trying Attempt #' . $attempts);
            }
        }
    }
}
