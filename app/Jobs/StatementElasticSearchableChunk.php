<?php

namespace App\Jobs;

use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JsonException;

class StatementElasticSearchableChunk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $min,
        public int $max,
        public int $chunk,
        public bool $range = true,
        public bool $benchmark = false,
    ) {}

    /**
     * Execute the job.
     *
     * @throws JsonException
     */
    public function handle(StatementElasticSearchService $statement_elastic_search_service): void
    {
        // Set this in cache, to emergency stop reindexing.
        $stop = Cache::get('stop_reindexing', false);
        if (! $stop) {
            $attempt = $this->attempts();
            $end = $this->min + $this->chunk;

            if ($end > $this->max) {
                $end = $this->max;
            }

            // Dispatch the next one
            if ($end < $this->max && $attempt === 1) {
                $next_min = $this->min + $this->chunk + 1;
                // Start the next one.
                self::dispatch($next_min, $this->max, $this->chunk, $this->range, $this->benchmark);
            } elseif ($end < $this->max) {
                Log::info('StatementElasticSearchableChunk skipped dispatch on retry', [
                    'min' => $this->min,
                    'max' => $this->max,
                    'end' => $end,
                    'chunk' => $this->chunk,
                    'range' => $this->range,
                    'benchmark' => $this->benchmark,
                    'attempt' => $attempt,
                ]);
            }

            $fetch_start = hrtime(true);
            if ($this->range) {
                $range = range($this->min, $end);
                $statements = Statement::query()->whereIn('id', $range)->get();
            } else {
                $statements = Statement::query()
                    ->whereBetween('id', [$this->min, $end])
                    ->orderBy('id')
                    ->get();
            }
            $fetch_ms = round((hrtime(true) - $fetch_start) / 1_000_000, 3);

            if ($this->benchmark) {
                $metrics = $statement_elastic_search_service->benchmarkBulkIndexStatements($statements);
                $metrics['fetch_ms'] = $fetch_ms;
                $metrics['total_ms'] += $fetch_ms;

                Log::info('StatementElasticSearchableChunk benchmark', array_merge([
                    'min' => $this->min,
                    'max' => $this->max,
                    'end' => $end,
                    'chunk' => $this->chunk,
                    'range' => $this->range,
                    'attempt' => $attempt,
                ], $metrics));
            } else {
                $statement_elastic_search_service->bulkIndexStatements($statements);
            }

            if ($end >= $this->max) {
                Log::info('StatementElasticSearchableChunk Max Reached at '.Carbon::now()->format('Y-m-d H:i:s'));
            }
        }
    }
}
