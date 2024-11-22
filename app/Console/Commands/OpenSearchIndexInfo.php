<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;

/**
 * @codeCoverageIgnore
 */
class OpenSearchIndexInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index-info {index}';

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

        $stats = $client->indices()->stats();
        $indices = $stats['indices'];

        if (!isset($indices[$index])) {
            $this->warn('The index is not in the indices stats, probably you used an alias?');
            return;
        }

        $index_stats = $indices[$index];

        $this->info('UUID: ' . $index_stats['uuid']);
        $this->info('Documents: ' . $index_stats['primaries']['docs']['count']);
        $this->info('Size: ' . $this->humanFileSize($index_stats['total']['store']['size_in_bytes']));

        $mapping = $client->indices()->getMapping(['index' => $index]);

        $this->newLine();

        $this->info('Field Information:');


        $fields = [];
        foreach ($mapping[$index]['mappings']['properties'] as $field => $field_info)
        {
            $fields[] = [$field,$field_info['type']];
        }

        $this->table(['Field', 'Type'], $fields);

        $shards = $client->cat()->shards(['index' => $index]);

        $shards_report = [];
        foreach ($shards as $shard) {
            if ($shard['prirep'] === 'p') {
                $shards_report[$shard['shard']]= [$shard['shard'], $shard['state'], $shard['docs'], $shard['store']];
            }
        }

        ksort($shards_report);

        $this->newLine();
        $this->info('Shards:');
        $this->table(['Shard', 'State', 'Docs', 'Store'], $shards_report);


        $alias = $client->indices()->getAlias(['index' => $index]);
        $aliases = array_keys($alias[$index]['aliases']);
        $out = [];
        foreach ($aliases as $alias) {
            $out[] = [
                'alias' => $alias
            ];
        }

        $this->newLine();
        $this->info('Aliases:');
        $this->table(['Alias'], $out);

        $this->newLine();

        $this->info('UUID: ' . $index_stats['uuid']);
        $this->info('Documents: ' . $index_stats['primaries']['docs']['count']);
        $this->info('Size: ' . $this->humanFileSize($index_stats['total']['store']['size_in_bytes']));

    }

    private function humanFileSize($size,$unit="") {
        if ((!$unit && $size >= 1<<30) || $unit == "GB") {
            return number_format($size/(1<<30),2)."GB";
        }

        if ((!$unit && $size >= 1<<20) || $unit == "MB") {
            return number_format($size/(1<<20),2)."MB";
        }

        if ((!$unit && $size >= 1<<10) || $unit == "KB") {
            return number_format($size/(1<<10),2)."KB";
        }

        return number_format($size)." bytes";
    }
}
