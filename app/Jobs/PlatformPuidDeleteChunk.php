<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlatformPuidDeleteChunk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $min, public int $max, public int $chunk) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
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
        DB::table('platform_puids')->whereIn('id', $range)->delete();

        if ($end >= $this->max) {
            Log::info('PlatformPuidDeleteChunk Max Reached at '.Carbon::now()->format('Y-m-d H:i:s'));
        }
    }
}
