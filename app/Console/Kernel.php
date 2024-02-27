<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private const string DAILY_AFTER_MIDNIGHT = '00:10';

    private const string DAILY_ONE_PM = '13:00';

    private const string DAILY_ONE_O_ONE_PM = '13:01';

    private const string DAILY_ONE_O_TWO_PM = '13:02';

    private const string DAILY_ONE_O_THREE_PM = '13:03';

    private const string DAILY_ONE_O_FOUR_PM = '13:04';

    #[\Override]
    protected function schedule(Schedule $schedule): void
    {
        // The main indexer run daily after midnight.
        $schedule->command('statements:index-date')->dailyAt(self::DAILY_AFTER_MIDNIGHT);

        // Home page caching
        $schedule->command('enrich-home-page-cache --grandtotal')->dailyAt(self::DAILY_ONE_PM);
        $schedule->command('enrich-home-page-cache --automateddecisionspercentage')->dailyAt(self::DAILY_ONE_O_ONE_PM);
        $schedule->command('enrich-home-page-cache --topcategories')->dailyAt(self::DAILY_ONE_O_TWO_PM);
        $schedule->command('enrich-home-page-cache --topdecisionsvisibility')->dailyAt(self::DAILY_ONE_O_THREE_PM);
        $schedule->command('enrich-home-page-cache --platformstotal')->dailyAt(self::DAILY_ONE_O_FOUR_PM);
    }

    // Existing `commands` method remains unchanged.
    #[\Override]
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        //$this->load(__DIR__ . '/Commands/Setup');
        require base_path('routes/console.php');
    }
}
