<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Models\Statement;
use App\Services\PlatformDayTotalsService;
use Illuminate\Console\Command;

class CompilePlatformDayTotalsDecisions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:compile-day-totals-decisions {platform_id=all} {days=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue all day totals for a platform and decisions.';

    /**
     * Execute the console command.
     */
    public function handle(PlatformDayTotalsService $platform_day_totals_service)
    {
        $platform_id = $this->argument('platform_id');
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
            foreach (Statement::DECISION_VISIBILITIES as $key => $description) {
                $platform_day_totals_service->compileDayTotals($platform, 'decision_visibility', $key, $days);
            }

            foreach (Statement::DECISION_MONETARIES as $key => $description) {
                $platform_day_totals_service->compileDayTotals($platform, 'decision_monetary', $key, $days);
            }

            foreach (Statement::DECISION_PROVISIONS as $key => $description) {
                $platform_day_totals_service->compileDayTotals($platform, 'decision_provision', $key, $days);
            }

            foreach (Statement::DECISION_ACCOUNTS as $key => $description) {
                $platform_day_totals_service->compileDayTotals($platform, 'decision_account', $key, $days);
            }

        }
    }
}
