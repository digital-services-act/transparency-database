<?php

namespace App\Console\Commands;

use App\Models\PlatformDayTotal;
use App\Services\PlatformDayTotalsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecompileAllDayTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:recompile-allday-totals {start=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue all day totals for all platforms to be recompiled.';

    /**
     * Execute the console command.
     */
    public function handle(PlatformDayTotalsService $platform_day_totals_service): void
    {
        $start = $this->argument('start') === 'default' ? '2023-09-25 00:00:00' : Carbon::createFromFormat('Y-m-d', $this->argument('start'))->format('Y-m-d 00:00:00');
        $platform_day_totals = PlatformDayTotal::query()->where('date', '>=', $start)->get();
        foreach ($platform_day_totals as $platform_day_total) {
            \App\Jobs\CompilePlatformDayTotal::dispatch(
                $platform_day_total->platform_id,
                $platform_day_total->date->format('Y-m-d'),
                $platform_day_total->attribute,
                $platform_day_total->value,
                true
            );
        }
    }
}
