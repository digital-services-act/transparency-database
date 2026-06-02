<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private const string DAILY_AFTER_MIDNIGHT = '00:10';

    private const string DAILY_TEST = '07:30';

    private const string DAILY_NINE_AM = '09:00';

    private const string DAILY_NINE_O_ONE_AM = '09:01';

    private const string DAILY_NINE_O_TWO_AM = '09:02';

    private const string DAILY_NINE_O_THREE_AM = '09:03';

    private const string DAILY_NINE_O_FOUR_AM = '09:04';

    private const string DAILY_FIVE_AM = '05:00';
    private const string DAILY_TWO_AM = '02:00';
    private const string DAILY_SIX_AM = '06:00';

    private const string DAILY_EIGHT_O_FIVE_AM = '08:05';

    private const string DAILY_EIGHT_TEN_AM = '08:10';

    private const string DAILY_EIGHT_FIFTEEN_AM = '08:15';

    #[\Override]
    protected function schedule(Schedule $schedule): void
    {
        // We should only be running crons on the task server.
        if (config('app.is_task_server')) {
            
            $schedule->command('statements:prune-old')
                ->dailyAt(self::DAILY_TWO_AM)
                ->withoutOverlapping()
                ->runInBackground();

            $schedule->command('statements:day-archive-z')
                ->dailyAt(self::DAILY_SIX_AM);

            $schedule->command('aggregates-freeze 160')
                ->dailyAt(self::DAILY_EIGHT_O_FIVE_AM);

            $schedule->command('aggregates-freeze 20')
                ->dailyAt(self::DAILY_EIGHT_TEN_AM);

            $schedule->command('aggregates-freeze yesterday')
                ->dailyAt(self::DAILY_EIGHT_FIFTEEN_AM);

            if (config('app.env') === 'production') {
                
                // Home page caching once daily at 9
                $schedule->command('enrich-home-page-cache --grandtotal')->dailyAt(self::DAILY_NINE_AM);
                
                // Indexing nightly on production, on other envs it runs at create statement time
                $schedule->command('statements:elastic-index-date-seq yesterday 1000 false 8')->dailyAt(self::DAILY_AFTER_MIDNIGHT);

            } else {

                // On non-production environments, run the home page cache enrichment more frequently for testing purposes.
                $schedule->command('enrich-home-page-cache --grandtotal')->everyFiveMinutes();
            }

            // Update these caches daily at 9:01, 9:02, 9:03, and 9:04 respectively to stagger the load on the system.
            $schedule->command('enrich-home-page-cache --automateddecisionspercentage')->dailyAt(self::DAILY_NINE_O_ONE_AM);
            $schedule->command('enrich-home-page-cache --topcategories')->dailyAt(self::DAILY_NINE_O_TWO_AM);
            $schedule->command('enrich-home-page-cache --topdecisionsvisibility')->dailyAt(self::DAILY_NINE_O_THREE_AM);
            $schedule->command('enrich-home-page-cache --platformstotal')->dailyAt(self::DAILY_NINE_O_FOUR_AM);

        }
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
