<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private const DAILY_3AM = '03:00';
    private const DAILY_1AM = '01:00';

    protected function schedule(Schedule $schedule): void
    {
        // The main indexer
        $schedule->command('statements:index-last-x')->everyMinute();

        // Verify and fix things from yesterday
        $schedule->command('statements:verify-index-date')->dailyAt(self::DAILY_1AM);

        // Now the Day Totals
        $this->scheduleCommandsToRunDaily($schedule);
    }

    private function scheduleCommandsToRunDaily(Schedule $schedule): void
    {
        $schedule->command('platform:compile-day-totals')->dailyAt(self::DAILY_3AM);
        $schedule->command('platform:compile-day-totals all decision_ground DECISION_GROUND_ILLEGAL_CONTENT')->dailyAt(self::DAILY_3AM);
        $schedule->command('platform:compile-day-totals all decision_ground DECISION_GROUND_INCOMPATIBLE_CONTENT')->dailyAt(self::DAILY_3AM);
        $schedule->command('platform:compile-day-totals-categories')->dailyAt(self::DAILY_3AM);
        $schedule->command('platform:compile-day-totals-keywords')->dailyAt(self::DAILY_3AM);
        $schedule->command('platform:compile-day-totals-decisions')->dailyAt(self::DAILY_3AM);
    }

    // Existing `commands` method remains unchanged.
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
