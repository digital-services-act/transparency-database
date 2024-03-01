<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;
use Symfony\Component\VarDumper\VarDumper;

class OpenSearchIndexSettingsRefreshInterval extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index-settings-refresh-interval {index} {interval}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the replicas for an index. Should be 0 or 2';

    /**
     * Execute the console command.
     */
    public function handle(Client $client): void
    {
        $index = $this->argument('index');
        $interval = $this->intifyArgument('interval');

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



        $client->indices()->putSettings([
            'index' => $index,
            'body' => [
                'refresh_interval' => $interval . 's'
            ]
        ]);

        $index_settings = $client->indices()->getSettings(['index' => $index]);
        VarDumper::dump($index_settings);
    }

}
