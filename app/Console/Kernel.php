<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private const DAILY_AFTER_MIDNIGHT = '00:10';
    private const DAILY_SEVEN_AM = '07:00';

    protected function schedule(Schedule $schedule): void
    {
        // The main indexer
        $schedule->command('statements:index-date')->dailyAt(self::DAILY_AFTER_MIDNIGHT);
        $schedule->command('enrich-home-page-cache --invalidate')->dailyAt(self::DAILY_SEVEN_AM);
    }



    // Existing `commands` method remains unchanged.
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
