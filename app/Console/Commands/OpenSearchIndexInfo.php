<?php

namespace App\Console\Commands;

use App\Jobs\SpamStatementCreation;
use App\Jobs\StatementCreation;
use App\Models\Statement;
use Illuminate\Console\Command;
use Illuminate\Console\View\Components\Info;
use OpenSearch\Client;

class OpenSearchIndexInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index_info {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get some info about the open search.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (env('SCOUT_DRIVER', '') !== 'opensearch')
        {
            $this->error('opensearch is not the SCOUT_DRIVER');
            return;
        }

        $index = $this->argument('index');
        if (!$index)
        {
            $this->error('index argument required');
            return;
        }
        /** @var Client $client */
        $client  = app(Client::class);

        if (!$client->indices()->exists(['index' => $index]))
        {
            $this->error('index does not exist');
            return;
        }

        $index_stats = $client->indices()->stats()['indices'][$index];
        $this->info('UUID: ' . $index_stats['uuid']);
        $this->info('Documents: ' . $index_stats['primaries']['docs']['count']);
        $this->info('Size: ' . $this->humanFileSize($index_stats['primaries']['store']['size_in_bytes']));

        $mapping = $client->indices()->getMapping(['index' => $index]);

        $this->newLine();

        $this->info('Field Information:');

        foreach ($mapping[$index]['mappings']['properties'] as $field => $field_info)
        {
            $this->info($field . ' :: ' . $field_info['type']);
        }
    }

    private function humanFileSize($size,$unit="") {
        if( (!$unit && $size >= 1<<30) || $unit == "GB")
            return number_format($size/(1<<30),2)."GB";
        if( (!$unit && $size >= 1<<20) || $unit == "MB")
            return number_format($size/(1<<20),2)."MB";
        if( (!$unit && $size >= 1<<10) || $unit == "KB")
            return number_format($size/(1<<10),2)."KB";
        return number_format($size)." bytes";
    }
}
