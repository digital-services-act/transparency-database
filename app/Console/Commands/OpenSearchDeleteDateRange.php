<?php

namespace App\Console\Commands;

use App\Jobs\OpenSearchDeleteBatch;
use Illuminate\Console\Command;
use OpenSearch\Client;

class OpenSearchDeleteDateRange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:delete-date-range
        {start : Start date (Y-m-d)}
        {end : End date (Y-m-d)}
        {--platform_id= : Platform ID (optional)}
        {--batch=2500 : Batch size per delete}
        {--index=statement_index : OpenSearch index name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete OpenSearch records by date range (optional platform_id)';

    public function __construct(protected Client $opensearch)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $index = $this->option('index');
        $start = $this->argument('start');
        $end = $this->argument('end');
        $platformId = $this->option('platform_id');
        $batchSize = (int) $this->option('batch');

        // Check if write blocks
        $settings = $this->opensearch->indices()->getSettings(['index' => $index]);
        $blocks = $settings[$index]['settings']['index'] ?? [];

        if (($blocks['blocks']['read_only_allow_delete'] ?? 'false') === 'true') {
            $this->warn("⚠️ Index {$index} is read-only. Removing block...");
            $this->unblockIndex($index);
        }

        $must = [];
        $must[] = [
            'range' => [
                'created_at' => [
                    'gte' => "{$start}T00:00:00",
                    'lte' => "{$end}T23:59:59"
                ],
            ],
        ];
        if ($platformId) {
            $must[] = ['term' => ['platform_id' => (int)$platformId]];
        }

        $query = ['bool' => ['must' => $must ?: [['match_all' => (object)[]]]]];
        $count = $this->opensearch->count(['index' => $index, 'body' => ['query' => $query]])['count'];

        if (!$count) {
            $this->warn("No documents match.");
            return;
        }

        $this->info("🔍 Found {$count} documents in {$index} to delete.");

        $batches = ceil($count / $batchSize);

        for ($i = 1; $i <= $batches; $i++) {
            OpenSearchDeleteBatch::dispatch($index, $query, $batchSize)->delay(now()->addSeconds($i * 5));
            $this->line("📤 Dispatched batch {$i}/{$batches}");
        }
    }

    protected function unblockIndex(string $index)
    {
        if (env('APP_ENV_REAL') !== 'production') {
            $this->opensearch->indices()->putSettings([
                'index' => $index,
                'body'  => [
                    'settings' => [
                        'index.blocks.read_only_allow_delete' => false
                    ],
                ]
            ]);
        }
    }
}
