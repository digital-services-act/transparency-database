<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class ElasticSearchIndexAliasDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-alias-delete {index} {alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an alias on an Elasticsearch Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client = app(StatementElasticSearchService::class)->client();
        $index = $this->argument('index');
        $alias = $this->argument('alias');

        if ($client->indices()->exists(['index' => $index])->asBool()) {
            if ($client->indices()->existsAlias(['index' => $index, 'name' => $alias])->asBool()) {
                $client->indices()->deleteAlias(['index' => $index, 'name' => $alias]);
            } else {
                $this->warn('Alias does not exists on this index!');
            }
        } else {
            $this->warn('Index does not exist!');
        }
    }
}
