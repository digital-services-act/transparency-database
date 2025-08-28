<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class ElasticSearchIndexReindex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-reindex {index} {target}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex an index into another Elasticsearch Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client = app(StatementElasticSearchService::class)->client();
        $index = $this->argument('index');
        $target = $this->argument('target');

        if (! $client->indices()->exists(['index' => $index])->asBool()) {
            $this->warn('Source index does not exist!');

            return;
        }

        if (! $client->indices()->exists(['index' => $target])->asBool()) {
            $this->warn('Target index does not exist!');

            return;
        }

        $result = $client->reindex([
            'wait_for_completion' => false,
            'body' => [
                'conflicts' => 'proceed',
                'source' => [
                    'index' => $index,
                ],
                'dest' => [
                    'index' => $target,
                    'op_type' => 'create',
                ],
            ],
        ]);
    }
}
