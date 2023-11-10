<?php

namespace App\Jobs;

use App\Models\Platform;
use App\Services\PlatformDayTotalsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CompilePlatformDayTotal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $platform_id;
    public string $date; // Y-m-d
    public string $attribute;
    public string $value;

    /**
     * Create a new job instance.
     */
    public function __construct($platform_id, $date, $attribute = '*', $value = '*')
    {
        $this->platform_id = $platform_id;
        $this->date = $date;
        $this->attribute = $attribute;
        $this->value = $value;
    }

    /**
     * Execute the job.
     */
    public function handle(PlatformDayTotalsService $platform_day_totals_service): void
    {

        $platform = Platform::find($this->platform_id);
        $date = Carbon::createFromFormat('Y-m-d', $this->date);

        if ($platform && $date) {
            $platform_day_totals_service->compileDayTotal($platform, $date, $this->attribute, $this->value);
        }
    }
}
