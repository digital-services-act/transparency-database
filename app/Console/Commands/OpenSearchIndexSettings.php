<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @codeCoverageIgnore
 */
class OpenSearchIndexSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index-settings {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get some info about the open search.';

    /**
     * Execute the console command.
     */
    public function handle(Client $client): void
    {
        $index = $this->argument('index');
        if (!$index)
        {
            $this->error('index argument required');
            return;
        }

        if (!$client->indices()->exists(['index' => $index])) {
            $this->error('index does not exist');
            return;
        }

        $index_settings = $client->indices()->getSettings(['index' => $index]);

        VarDumper::dump($index_settings);
    }

}
