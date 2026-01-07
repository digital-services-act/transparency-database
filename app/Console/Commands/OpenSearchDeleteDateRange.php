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

    public const CONCURRENT_DELETES = 4;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:delete-date-range
        {start : Start date (Y-m-d)}
        {end? : End date (Y-m-d)}
        {--platform_id= : Platform ID (optional)}
        {--batch=5000 : Batch size per delete}';

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

        $start = $this->sanitizeDateArgument('start');

        if (!$end) {
            $end = $start;
        } else {
            $end = $this->sanitizeDateArgument('end');
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

        // We'll run multiple delete threads in parallel as it speeds things up on the OS
        $parts = self::CONCURRENT_DELETES;
        $partSize = (int) ceil($count / $parts);

        for ($i = 1; $i <= $parts; $i++) {
            try {
                $this->opensearch->deleteByQuery([
                    'index'     => $this->index,
                    'conflicts' => 'proceed',
                    'body'      => ['query' => $query],
                    'size'      => $partSize,
                    'refresh' => true,
                    'wait_for_completion' => false,
                ]);
                $this->line("📤 Running delete batch {$i}/{$parts} (size={$partSize})");
            } catch (\Throwable $e) {
                logger()->error('OpenSearch deleteByQuery failed', [
                    'batch' => $i,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Batch {$i} failed: {$e->getMessage()}");
            }
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
