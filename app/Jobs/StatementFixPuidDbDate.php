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
class StatementFixPuidDbDate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    protected $platformId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $platformId, string $date)
    {
        $this->date = $date;
        $this->platformId = $platformId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startBatch = Carbon::now();

        try {
            $start = Carbon::parse($this->date)->startOfDay();
            $end   = Carbon::parse($this->date)->endOfDay();
        } catch (\Exception $e) {
            Log::error("Invalid date supplied to StatementFixPuidDbDate: {$this->date}");
            throw new \InvalidArgumentException("Invalid date supplied");
        }

        $platform = Platform::find($this->platformId);
        if (!$platform) {
            Log::error("Could not find platform with ID {$this->platformId}");
            throw new \InvalidArgumentException("Invalid date supplied");
        }

        $table = $start->lt('2025-07-01 00:00:00') ? 'statements' : 'statements_beta';

        $ids = DB::connection('mysql::read')
            ->table($table)
            ->select(DB::raw("min(id) as min_id, max(id) as max_id"))
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->first();

        if (empty($ids) || $ids->min_id === null || $ids->max_id === null) {
            Log::info("No statements found for platform {$platform->name} on {$this->date}");
            return;
        }

        try {
            DB::connection('mysql::read')
                ->table($table)
                ->whereBetween('id', [$ids->min_id, $ids->max_id])
                ->where('platform_id', $platform->id)
                ->update([
                    'puid' => DB::raw("SUBSTRING_INDEX(puid, '-', 1)")
                ]);

            Log::info("StatementFixPuidDbDate for {$this->date} ended " . Carbon::now()->diffForHumans($startBatch));
        } catch (\Exception $e) {
            Log::error("Error in StatementFixPuidDbDate for {$this->date}: " . $e->getMessage());
        }
    }
}
