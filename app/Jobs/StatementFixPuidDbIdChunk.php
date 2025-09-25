<?php

namespace App\Jobs;

use App\Models\Platform;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
**/
class StatementFixPuidDbIdChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $chunk = 1000;
    /**
     * Create a new job instance.
     */
    public function __construct(public int $platformId, public int $min, public int $max, public string $table)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $platform = Platform::find($this->platformId);
        if (!$platform) {
            Log::error("Could not find platform with ID {$this->platformId}");
            throw new \InvalidArgumentException("Invalid date supplied");
        }

        $startBatch = Carbon::now();

        $end = $this->min + $this->chunk;

        if ($end > $this->max) {
            $end = $this->max;
        }

        if ($end < $this->max) {
            $nextMin = $this->min + $this->chunk + 1;
            self::dispatch($this->platformId, $nextMin, $this->max, $this->table);
        }

        $range = range($this->min, $end);

        try {
            DB::connection('mysql::read')
                ->table($this->table)
                ->whereIn('id', $range)
                ->where('platform_id', $platform->id)
                ->update([
                    'puid' => DB::raw("SUBSTRING_INDEX(puid, '-', 1)")
                ]);

            Log::info("StatementFixPuidDbDate for ids {$this->min} to {$end} ended " . Carbon::now()->diffForHumans($startBatch));
        } catch (\Exception $e) {
            Log::error("Error in StatementFixPuidDbDate for {$this->min} to {$end}: " . $e->getMessage());
        }
    }
}
