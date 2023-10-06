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
        $schedule->command('platform:compile-day-totals')->daily();
        $schedule->command('platform:compile-day-totals all decision_ground DECISION_GROUND_ILLEGAL_CONTENT')->daily();
        $schedule->command('platform:compile-day-totals all decision_ground DECISION_GROUND_INCOMPATIBLE_CONTENT')->daily();
        $schedule->command('platform:compile-day-totals-categories')->daily();
        $schedule->command('platform:compile-day-totals-keywords')->daily();
        $schedule->command('platform:compile-day-totals-decisions')->daily();
        $schedule->command('statements:day-archive')->daily();

        /*

          Put any typical commands that need to be run after dev/local reset-application here so that a dev can have first initial db..

        php artisan statements:optimize-index &&
        php artisan platform:compile-day-totals &&
        php artisan platform:compile-day-totals all decision_ground DECISION_GROUND_ILLEGAL_CONTENT &&
        php artisan platform:compile-day-totals all decision_ground DECISION_GROUND_INCOMPATIBLE_CONTENT &&
        php artisan platform:compile-day-totals-categories &&
        php artisan platform:compile-day-totals-keywords &&
        php artisan platform:compile-day-totals-decisions

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
