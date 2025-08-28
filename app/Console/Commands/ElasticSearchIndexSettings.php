<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @codeCoverageIgnore
 */
class ElasticSearchIndexSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-settings {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get some info about the elasticsearch.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client = app(StatementElasticSearchService::class)->client();
        $index = $this->argument('index');
        if (! $index) {
            $this->error('index argument required');

            return;
        }

        if (! $client->indices()->exists(['index' => $index])->asBool()) {
            $this->error('index does not exist');

            return;
        }

        $index_settings = $client->indices()->getSettings(['index' => $index])->asArray();

        VarDumper::dump($index_settings);
    }
}
