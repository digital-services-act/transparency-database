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

class StatementDeleteChunk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $min, public int $max, public int $chunk, public string $date) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $attempt = $this->attempts();
        $end = $this->min + $this->chunk;

        if ($end > $this->max) {
            $end = $this->max;
        }

        // Dispatch the next one
        if ($end < $this->max && $attempt === 1) {
            $next_min = $this->min + $this->chunk + 1;
            // Start the next one.
            self::dispatch($next_min, $this->max, $this->chunk, $this->date);
        } elseif ($end < $this->max) {
            Log::info('StatementDeleteChunk skipped dispatch on retry', [
                'min' => $this->min,
                'max' => $this->max,
                'end' => $end,
                'chunk' => $this->chunk,
                'date' => $this->date,
                'attempt' => $attempt,
            ]);
        }

        $startOfDay = Carbon::parse($this->date)->startOfDay();

        DB::table('statements_beta')
            ->where('id', '>=', $this->min)
            ->where('id', '<=', $end)
            ->where('created_at', '>=', $startOfDay)
            ->where('created_at', '<', $startOfDay->copy()->addDay())
            ->delete();

        if ($end >= $this->max) {
            Log::info('StatementDeleteChunk Max Reached at '.Carbon::now()->format('Y-m-d H:i:s'));
        }
    }
}
