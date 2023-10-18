<?php

namespace App\Console\Commands;

use App\Services\ContentDateAggregateService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ContentDateAggregate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contentdateaggregate:compile {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the content date aggregate.';


    /**
     * @throws Exception
     */
    public function handle(ContentDateAggregateService $content_date_aggregate_service)
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
        $content_date_aggregate_service->compileDayTotals($date);
    }
}
