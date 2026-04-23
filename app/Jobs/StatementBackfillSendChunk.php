<?php

namespace App\Jobs;

use App\Services\StatementBackfillTargetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
 */
class StatementBackfillSendChunk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $min, public int $max, public int $chunk)
    {
        $queue = (string) config('backfill.queue');
        if ($queue !== '') {
            $this->onQueue($queue);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(StatementBackfillTargetService $backfillTargetService): void
    {
        $end = min($this->min + $this->chunk - 1, $this->max);

        

        $rows = DB::table($backfillTargetService->getConfiguredTable())
            ->whereBetween('id', [$this->min, $end])
            ->orderBy('id')
            ->get();

        $statements = array_map(static fn($row) => (array) $row, $rows->all());

        if ($statements !== []) {
            $backfillTargetService->sendStatements($statements);

            Log::info('StatementBackfillSendChunk sent rows', [
                'range_start' => $this->min,
                'range_end' => $end,
                'row_count' => count($statements),
                'first_statement_id' => $statements[0]['id'] ?? null,
                'last_statement_id' => $statements[count($statements) - 1]['id'] ?? null,
            ]);
        } else {
            Log::info('StatementBackfillSendChunk found no rows in range', [
                'range_start' => $this->min,
                'range_end' => $end,
            ]);
        }

        // Queue the next range.
        if ($end < $this->max) {
            self::dispatch($end + 1, $this->max, $this->chunk);
        } else {
            Log::info('StatementBackfillSendChunk max reached at ' . Carbon::now()->format('Y-m-d H:i:s'), [
                'range_end' => $end,
            ]);
        }
        
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }
}
