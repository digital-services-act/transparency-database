<?php

namespace App\Console\Commands;

use App\Jobs\OpenSearchDeleteBatch;
use Illuminate\Console\Command;
use OpenSearch\Client;

/**
 * @codeCoverageIgnore
 */
class OpenSearchDeleteDateRange extends Command
{
    use CommandTrait;

    protected $index = 'statement_index';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:delete-date-range
        {start : Start date (Y-m-d)}
        {end? : End date (Y-m-d)}
        {--platform_id= : Platform ID (optional)}
        {--batch=3000 : Batch size per delete}
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
        $start = $this->argument('start');
        $end = $this->argument('end');
        $platformId = $this->option('platform_id');
        $batchSize = (int) $this->option('batch');

        $start = $this->sanitizeDateArgument('start');

        if (!$end) {
            $end = $start;
        } else {
            $end = $this->sanitizeDateArgument('end');
        }

        if (env('APP_ENV_REAL') === 'dev') {
            $this->unblockIndex();
        }

        $must = [];
        $must[] = [
            'range' => [
                'created_at' => [
                    'gte' => $start->startOfDay()->getTimestampMs(),
                    'lte' => $end->endOfDay()->getTimestampMs(),
                ],
            ],
        ];
        if ($platformId) {
            $must[] = ['term' => ['platform_id' => (int)$platformId]];
        }

        $query = ['bool' => ['must' => $must ?: [['match_all' => (object)[]]]]];
        $count = $this->opensearch->count(['index' => $this->index, 'body' => ['query' => $query]])['count'];

        if (!$count) {
            $this->warn("No documents match.");
            return;
        }

        $this->info("🔍 Found {$count} documents in {$this->index} to delete.");

        $batches = ceil($count / $batchSize);

        for ($i = 1; $i <= $batches; $i++) {
            OpenSearchDeleteBatch::dispatch($this->index, $query, $batchSize)->delay(now()->addSeconds($i * 5));
            $this->line("📤 Dispatched batch {$i}/{$batches}");
        }
    }

    protected function unblockIndex(): void
    {
        // Check if write blocks
        $settings = $this->opensearch->indices()->getSettings(['index' => $this->index]);
        $blocks = $settings[$this->index]['settings']['index'] ?? [];

        if (($blocks['blocks']['read_only_allow_delete'] ?? 'false') === 'true') {
            $this->warn("⚠️ Index {$this->index} is read-only. Removing block...");
            $this->opensearch->indices()->putSettings([
                'index' => $this->index,
                'body'  => [
                    'settings' => [
                        'index.blocks.read_only_allow_delete' => false
                    ],
                ]
            ]);
        }
    }
}
