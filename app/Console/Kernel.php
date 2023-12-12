<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('platform:compile-day-totals')->dailyAt('03:00');
        $schedule->command('platform:compile-day-totals all decision_ground DECISION_GROUND_ILLEGAL_CONTENT')->dailyAt('03:00');
        $schedule->command('platform:compile-day-totals all decision_ground DECISION_GROUND_INCOMPATIBLE_CONTENT')->dailyAt('03:00');
        $schedule->command('platform:compile-day-totals-categories')->dailyAt('03:00');
        $schedule->command('platform:compile-day-totals-keywords')->dailyAt('03:00');
        $schedule->command('platform:compile-day-totals-decisions')->dailyAt('03:00');

        // These ones run on a separate machine long running process.
        //$schedule->command('statements:day-archive')->daily();

        /*

          Put any typical commands that need to be run after dev/local reset-application here so that a dev can have first initial db..

            php artisan statements:optimize-index &&
            php artisan platform:compile-day-totals &&
            php artisan platform:compile-day-totals all decision_ground DECISION_GROUND_ILLEGAL_CONTENT &&
            php artisan platform:compile-day-totals all decision_ground DECISION_GROUND_INCOMPATIBLE_CONTENT &&
            php artisan platform:compile-day-totals-categories &&
            php artisan platform:compile-day-totals-keywords &&
            php artisan platform:compile-day-totals-decisions &&

            php artisan statements:day-archive
            php artisan statements:day-archive 2023-10-01
            php artisan statements:day-archive 2023-10-02
            php artisan statements:day-archive 2023-10-03
            ...

        */
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
