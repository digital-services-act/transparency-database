<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;

class OpensearchIndexAliasSwap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index-alias-swap {index} {target} {alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Swap an alias on an Opensearch index to another index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client     = app(Client::class);
        $index = $this->argument('index');
        $target = $this->argument('target');
        $alias = $this->argument('alias');

        if (!$client->indices()->exists(['index' => $index])) {
            $this->warn('Index does not exist!');
            return;
        }

        if (!$client->indices()->exists(['index' => $target])) {
            $this->warn('Target Index does not exist!');
            return;
        }

        if (!$client->indices()->existsAlias(['index' => $index, 'name' => $alias])) {
            $this->warn('Alias is not on the index!');
            return;
        }

        if ($client->indices()->existsAlias(['index' => $target, 'name' => $alias])) {
            $this->warn('Alias is already on the target index!');
            return;
        }

        $body = [
            'actions' => [
                [
                    'remove' => [
                        'index' => $index,
                        'alias' => $alias
                    ]
                ],
                [
                    'add' => [
                        'index' => $target,
                        'alias' => $alias
                    ]
                ]
            ]
        ];
        $client->indices()->updateAliases([
            'body' => $body
        ]);
    }
}
