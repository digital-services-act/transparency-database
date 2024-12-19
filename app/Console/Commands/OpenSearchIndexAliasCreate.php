<?php

namespace App\Console\Commands;

use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use OpenSearch\Client;

/**
 * @codeCoverageIgnore
 */
class OpenSearchIndexAliasCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index-alias-create {index} {alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an alias on an Opensearch Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client     = app(Client::class);
        $index = $this->argument('index');
        $alias = $this->argument('alias');

        if ($client->indices()->exists(['index' => $index])) {
            if (!$client->indices()->existsAlias(['index' => $index, 'name' => $alias])) {
                $client->indices()->putAlias(['index' => $index, 'name' => $alias]);
            } else {
                $this->warn('Alias already exists on this index!');
            }
        } else {
            $this->warn('Index does not exist!');
        }
    }
}
