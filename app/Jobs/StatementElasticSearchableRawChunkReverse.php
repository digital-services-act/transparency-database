<?php

namespace App\Jobs;

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

class StatementElasticSearchableRawChunkReverse implements ShouldQueue
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
            $end = $this->max - $this->chunk;

            if ($end < $this->min) {
                $end = $this->min;
            }

            // Dispatch the next one
            if ($end > $this->min && $attempt === 1) {
                $next_max = $this->max - $this->chunk - 1;
                // Start the next one.
                self::dispatch($this->min, $next_max, $this->chunk, $this->range, $this->benchmark);
            } elseif ($end > $this->min) {
                Log::info('StatementElasticSearchableRawChunkReverse skipped dispatch on retry', [
                    'min' => $this->min,
                    'max' => $this->max,
                    'end' => $end,
                    'chunk' => $this->chunk,
                    'range' => $this->range,
                    'benchmark' => $this->benchmark,
                    'attempt' => $attempt,
                ]);
            }

            if ($this->benchmark) {
                $metrics = $statement_elastic_search_service->benchmarkBulkIndexRawStatementsForIdRange(
                    $end,
                    $this->max,
                    $this->range,
                    'desc',
                );

                Log::info('StatementElasticSearchableRawChunkReverse benchmark', array_merge([
                    'min' => $this->min,
                    'max' => $this->max,
                    'end' => $end,
                    'chunk' => $this->chunk,
                    'range' => $this->range,
                    'attempt' => $attempt,
                ], $metrics));
            } else {
                $statement_elastic_search_service->bulkIndexRawStatementsForIdRange(
                    $end,
                    $this->max,
                    $this->range,
                    'desc',
                );
            }

            if ($end <= $this->min) {
                Log::info('StatementElasticSearchableRawChunkReverse Min Reached at '.Carbon::now()->format('Y-m-d H:i:s'));
            }
        }
    }
}
