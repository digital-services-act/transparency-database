<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * @codeCoverageIgnore
 */
class Kernel extends ConsoleKernel
{
    private const string DAILY_AFTER_MIDNIGHT = '00:10';

    private const string DAILY_TEST = '07:30';

    private const string DAILY_NINE_AM = '09:00';

    private const string DAILY_NINE_O_ONE_AM = '09:01';

    private const string DAILY_NINE_O_TWO_AM = '09:02';

    private const string DAILY_NINE_O_THREE_AM = '09:03';

    private const string DAILY_NINE_O_FOUR_AM = '09:04';

    #[\Override]
    protected function schedule(Schedule $schedule): void
    {
        // The main indexer run daily after midnight. Only on prod
        if (strtolower((string) config('app.env')) === 'production') {
            // Index the statements each night for the previous day.

            // 5 0 * * * php artisan statements:index-date-seq yesterday 2000
            $schedule->command('statements:elastic-index-date-seq yesterday 2000')
                ->dailyAt('00:05');

            // 0 5 * * * php artisan statements:archive-date
            $schedule->command('statements:remove-date')
                ->dailyAt('05:00');

            // 0 6 * * * php artisan statements:day-archive-z
            $schedule->command('statements:day-archive-z')
                ->dailyAt('06:00');

            // 0 6 * * * php artisan aggregates-freeze 160
            $schedule->command('aggregates-freeze 160')
                ->dailyAt('06:00');

            // 5 6 * * * php artisan aggregates-freeze 20
            $schedule->command('aggregates-freeze 20')
                ->dailyAt('06:05');

            // 10 6 * * * php artisan aggregates-freeze yesterday
            $schedule->command('aggregates-freeze yesterday')
                ->dailyAt('06:10');

            // Home page caching
            $schedule->command('enrich-home-page-cache --grandtotal')->dailyAt(self::DAILY_NINE_AM);
        } else {
            // Home page caching
            $schedule->command('enrich-home-page-cache --grandtotal')->everyFiveMinutes();
        }

        $schedule->command('enrich-home-page-cache --automateddecisionspercentage')->dailyAt(self::DAILY_NINE_O_ONE_AM);
        $schedule->command('enrich-home-page-cache --topcategories')->dailyAt(self::DAILY_NINE_O_TWO_AM);
        $schedule->command('enrich-home-page-cache --topdecisionsvisibility')->dailyAt(self::DAILY_NINE_O_THREE_AM);
        $schedule->command('enrich-home-page-cache --platformstotal')->dailyAt(self::DAILY_NINE_O_FOUR_AM);

    }

    // Existing `commands` method remains unchanged.
    #[\Override]
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        // $this->load(__DIR__ . '/Commands/Setup');
        require base_path('routes/console.php');
    }
}
