<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class ElasticSearchIndexAliasSwap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-alias-swap {index} {target} {alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Swap an alias on an Elasticsearch index to another index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client = app(StatementElasticSearchService::class)->client();
        $index = $this->argument('index');
        $target = $this->argument('target');
        $alias = $this->argument('alias');

        if (! $client->indices()->exists(['index' => $index])->asBool()) {
            $this->warn('Index does not exist!');

            return;
        }

        if (! $client->indices()->exists(['index' => $target])->asBool()) {
            $this->warn('Target Index does not exist!');

            return;
        }

        if (! $client->indices()->existsAlias(['index' => $index, 'name' => $alias])->asBool()) {
            $this->warn('Alias is not on the index!');

            return;
        }

        if ($client->indices()->existsAlias(['index' => $target, 'name' => $alias])->asBool()) {
            $this->warn('Alias is already on the target index!');

            return;
        }

        $body = [
            'actions' => [
                [
                    'remove' => [
                        'index' => $index,
                        'alias' => $alias,
                    ],
                ],
                [
                    'add' => [
                        'index' => $target,
                        'alias' => $alias,
                    ],
                ],
            ],
        ];
        $client->indices()->updateAliases([
            'body' => $body,
        ]);
    }
}
