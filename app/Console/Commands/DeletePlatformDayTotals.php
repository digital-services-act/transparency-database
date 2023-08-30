<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Models\PlatformDayTotal;
use App\Services\PlatformDayTotalsService;
use Illuminate\Console\Command;

class DeletePlatformDayTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:delete-day-totals {platform_id=all} {attribute=all} {value=all} {--nuclear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete all day totals for a platform.';

    /**
     * Execute the console command.
     */
    public function handle(PlatformDayTotalsService $platform_day_totals_service)
    {
        if ($this->option('nuclear'))
        {
            PlatformDayTotal::truncate();
            return;
        }

        $platform_id = $this->argument('platform_id');
        $attribute = $this->argument('attribute') !== 'all' ?  $this->argument('attribute') : '*';
        $value = $this->argument('value') !== 'all' ? $this->argument('value') : '*';

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
            $platform_day_totals_service->deleteDayTotals($platform, $attribute, $value);
        }
    }
}
