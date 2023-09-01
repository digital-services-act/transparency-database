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
        $schedule->command('platform:compile-day-totals all decision_visibility all')->daily();
        $schedule->command('platform:compile-day-totals all decision_monetary all')->daily();
        $schedule->command('platform:compile-day-totals all decision_provision all')->daily();
        $schedule->command('platform:compile-day-totals all decision_account all')->daily();
        $schedule->command('platform:compile-day-totals all decision_ground DECISION_GROUND_ILLEGAL_CONTENT')->daily();
        $schedule->command('platform:compile-day-totals all decision_ground DECISION_GROUND_INCOMPATIBLE_CONTENT')->daily();
        $schedule->command('platform:compile-day-totals-categories')->daily();
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
