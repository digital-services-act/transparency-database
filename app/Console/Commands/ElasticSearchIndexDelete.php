<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class ElasticSearchIndexDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-delete {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an Elasticsearch Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client     = app(StatementElasticSearchService::class)->client();
        $index = $this->argument('index');

        if ($client->indices()->exists(['index' => $index])->asBool()) {
            $client->indices()->delete(['index' => $index]);
        }
    }
}
