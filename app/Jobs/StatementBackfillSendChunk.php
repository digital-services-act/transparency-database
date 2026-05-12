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
use InvalidArgumentException;

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

    public const DIRECTION_ASC = 'asc';

    public const DIRECTION_DESC = 'desc';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $min,
        public int $max,
        public int $chunk,
        public string $direction = self::DIRECTION_ASC
    ) {
        $this->direction = self::normalizeDirection($direction);

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
        [$rangeStart, $rangeEnd] = $this->resolveRange();
        $attempt = $this->attempts();

        // Only the first attempt should fan out the next chunk.
        if ($this->hasNextChunk($rangeStart, $rangeEnd) && $attempt === 1) {
            $this->dispatchNextChunk($rangeStart, $rangeEnd);
        } elseif ($this->hasNextChunk($rangeStart, $rangeEnd)) {
            Log::info('StatementBackfillSendChunk skipped dispatch on retry', [
                'range_start' => $rangeStart,
                'range_end' => $rangeEnd,
                'direction' => $this->direction,
                'attempt' => $attempt,
            ]);
        } else {
            Log::info('StatementBackfillSendChunk boundary reached at '.Carbon::now()->format('Y-m-d H:i:s'), [
                'range_start' => $rangeStart,
                'range_end' => $rangeEnd,
                'direction' => $this->direction,
            ]);
        }

        $query = DB::table($backfillTargetService->getConfiguredTable())
            ->whereBetween('id', [$rangeStart, $rangeEnd]);

        if ($this->direction === self::DIRECTION_DESC) {
            $query->orderByDesc('id');
        } else {
            $query->orderBy('id');
        }

        $rows = $query->get();

        $statements = array_map(static fn ($row) => (array) $row, $rows->all());

        if ($statements !== []) {
            $backfillTargetService->sendStatements($statements);

            Log::info('StatementBackfillSendChunk sent rows', [
                'range_start' => $rangeStart,
                'range_end' => $rangeEnd,
                'direction' => $this->direction,
                'row_count' => count($statements),
                'first_statement_id' => $statements[0]['id'] ?? null,
                'last_statement_id' => $statements[count($statements) - 1]['id'] ?? null,
            ]);
        } else {
            Log::info('StatementBackfillSendChunk found no rows in range', [
                'range_start' => $rangeStart,
                'range_end' => $rangeEnd,
                'direction' => $this->direction,
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

    public static function normalizeDirection(string $direction): string
    {
        $direction = strtolower(trim($direction));

        if (! in_array($direction, [self::DIRECTION_ASC, self::DIRECTION_DESC], true)) {
            throw new InvalidArgumentException('Backfill direction must be asc or desc.');
        }

        return $direction;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function resolveRange(): array
    {
        if ($this->direction === self::DIRECTION_DESC) {
            return [
                max($this->max - $this->chunk + 1, $this->min),
                $this->max,
            ];
        }

        return [
            $this->min,
            min($this->min + $this->chunk - 1, $this->max),
        ];
    }

    private function hasNextChunk(int $rangeStart, int $rangeEnd): bool
    {
        if ($this->direction === self::DIRECTION_DESC) {
            return $rangeStart > $this->min;
        }

        return $rangeEnd < $this->max;
    }

    private function dispatchNextChunk(int $rangeStart, int $rangeEnd): void
    {
        if ($this->direction === self::DIRECTION_DESC) {
            self::dispatch($this->min, $rangeStart - 1, $this->chunk, $this->direction);

            return;
        }

        self::dispatch($rangeEnd + 1, $this->max, $this->chunk, $this->direction);
    }
}
