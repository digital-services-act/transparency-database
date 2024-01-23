<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Services\PlatformDayTotalsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeletePlatformDayTotal extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:delete-day-total {platform_id} {date} {attribute=all} {value=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a day total.';

    /**
     * Execute the console command.
     */
    public function handle(PlatformDayTotalsService $platform_day_totals_service): void
    {
        $platform_id = $this->argument('platform_id');
        $platforms = [];
        if ($platform_id === 'all') {
            $platforms = Platform::all();
        }

        if ($platform_id !== 'all') {
            $platform = Platform::find((int)$platform_id);
            if ($platform) {
                $platforms[] = $platform;
            }
        }
        $date = $this->sanitizeDateArgument();
        $attribute = $this->argument('attribute') !== 'all' ?  $this->argument('attribute') : '*';
        $value = $this->argument('value') !== 'all' ? $this->argument('value') : '*';

        if ($platforms) {
            foreach ($platforms as $platform) {
                $platform_day_totals_service->deleteDayTotal($platform, $date, $attribute, $value);
            }
        } else {
            $this->warn('The platform id were invalid.');
        }
    }
}
