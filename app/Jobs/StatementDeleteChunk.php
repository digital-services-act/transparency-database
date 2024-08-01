<?php

namespace App\Jobs;

use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StatementDeleteChunk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct(public int $min, public int $max, public int $chunk)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Set this in cache, to emergency stop reindexing.

            $end = $this->min + $this->chunk;

            if ($end > $this->max) {
                $end = $this->max;
            }


            // Dispatch the next one
            if ($end < $this->max) {
                $next_min = $this->min + $this->chunk + 1;
                // Start the next one.
                self::dispatch($next_min, $this->max, $this->chunk);
            }

            $range = range($this->min, $end);
            // Bulk indexing.
            $statements = Statement::query()->whereIn('id', $range)->delete();


            if ($end >= $this->max) {
                Log::info('StatementSearchableChunk Max Reached at ' . Carbon::now()->format('Y-m-d H:i:s'));
            }

    }
}
