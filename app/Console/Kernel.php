<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private const DAILY_AFTER_MIDNIGHT = '00:15';

    protected function schedule(Schedule $schedule): void
    {
        // The main indexer
        $schedule->command('statements:index-date')->dailyAt(self::DAILY_AFTER_MIDNIGHT);
    }



    // Existing `commands` method remains unchanged.
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
