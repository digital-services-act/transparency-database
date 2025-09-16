<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

class ElasticSearchIndexInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-info {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get some info about the elasticsearch.';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        $index = $this->argument('index');

        try {
            $indexInfo = $elasticSearchService->getIndexInfo($index);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'does not exist')) {
                $this->error('index does not exist');
            } else {
                $this->warn('The index is not in the indices stats, probably you used an alias?');
            }

            return;
        }

        $this->info('UUID: '.$indexInfo['uuid']);
        $this->info('Documents: '.$indexInfo['documents']);
        $this->info('Size: '.$this->humanFileSize($indexInfo['size_bytes']));

        $this->newLine();
        $this->info('Field Information:');
        $this->table(['Field', 'Type'], $indexInfo['fields']);

        $this->newLine();
        $this->info('Shards:');
        $this->table(['Shard', 'State', 'Docs', 'Store'], $indexInfo['shards']);

        $this->newLine();
        $this->info('Aliases:');
        $this->table(['Alias'], $indexInfo['aliases']);

        $this->newLine();
        $this->info('UUID: '.$indexInfo['uuid']);
        $this->info('Documents: '.$indexInfo['documents']);
        $this->info('Size: '.$this->humanFileSize($indexInfo['size_bytes']));
    }

    private function humanFileSize($size, $unit = '')
    {
        if ((! $unit && $size >= 1 << 30) || $unit == 'GB') {
            return number_format($size / (1 << 30), 2).'GB';
        }

        if ((! $unit && $size >= 1 << 20) || $unit == 'MB') {
            return number_format($size / (1 << 20), 2).'MB';
        }

        if ((! $unit && $size >= 1 << 10) || $unit == 'KB') {
            return number_format($size / (1 << 10), 2).'KB';
        }

        return number_format($size).' bytes';
    }
}
