<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Services\PlatformDayTotalsService;
use Illuminate\Console\Command;

class CompilePlatformDayTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:compile-day-totals {platform_id} {attribute=all} {value=all} {days=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue all day totals for a platform.';

    /**
     * Execute the console command.
     */
    public function handle(PlatformDayTotalsService $platform_day_totals_service)
    {
        $platform_id = $this->argument('platform_id');
        $attribute = $this->argument('attribute') !== 'all' ?  $this->argument('attribute') : '*';
        $value = $this->argument('value') !== 'all' ? $this->argument('value') : '*';
        $days = (int)$this->argument('days');

        $platforms = [];
        if ($platform_id === 'all') {
            $platforms = Platform::all();
        }

        if ($platform_id !== 'all') {
            $platform = Platform::find($platform_id);
            if ($platform) {
                $platforms[] = $platform;
            }
        }

        foreach ($platforms as $platform) {
            $platform_day_totals_service->compileDayTotals($platform, $attribute, $value, $days);
        }
    }
}
