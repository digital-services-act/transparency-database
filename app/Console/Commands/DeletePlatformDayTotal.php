<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Services\PlatformDayTotalsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeletePlatformDayTotal extends Command
{
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
    public function handle(PlatformDayTotalsService $platform_day_totals_service)
    {
        $platform_id = (int)$this->argument('platform_id');
        $date = $this->argument('date');
        $attribute = $this->argument('attribute') !== 'all' ?  $this->argument('attribute') : '*';
        $value = $this->argument('value') !== 'all' ? $this->argument('value') : '*';

        $platform = Platform::find($platform_id);
        $date = Carbon::createFromFormat('Y-m-d', $date);
        if ($platform && $date) {
            $platform_day_totals_service->deleteDayTotal($platform, $date, $attribute, $value);
        } else {
            $this->warn('The platform id or date were invalid.');
        }
    }
}
