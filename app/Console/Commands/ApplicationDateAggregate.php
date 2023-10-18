<?php

namespace App\Console\Commands;

use App\Services\ApplicationDateAggregateService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ApplicationDateAggregate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applicationdateaggregate:compile {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the application date aggregate.';


    /**
     * @throws Exception
     */
    public function handle(ApplicationDateAggregateService $application_date_aggregate_service)
    {
        $date = $this->argument('date');
        if ($date === 'yesterday') {
            $date = Carbon::yesterday();
        } else {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $date);
            } catch (Exception $e) {
                $this->error('Issue with the date provided, checked the format yyyy-mm-dd');
            }
        }
        $application_date_aggregate_service->compileDayTotals($date);
    }
}
