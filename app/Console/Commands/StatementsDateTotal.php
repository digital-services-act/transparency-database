<?php

namespace App\Console\Commands;

use App\Services\DayArchiveService;
use App\Services\StatementSearchService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class StatementsDateTotal extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:date-total {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a day archive compile jobs.';

    /**
     * Execute the console command.
     * @throws Exception
     * @throws Throwable
     */
    public function handle(DayArchiveService $day_archive_service, StatementSearchService $statement_search_service): void
    {
        $date        = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');

        $first_id = $day_archive_service->getFirstIdOfDate($date);
        $last_id  = $day_archive_service->getLastIdOfDate($date);

        if ($first_id && $last_id) {
            $this->info('Date: ' . $date_string);
            $this->info('First ID: ' . $first_id);
            $this->info('Last ID: ' . $last_id);
            $db_diff = $last_id - $first_id;
            $this->info('Difference in IDs: ' . $db_diff);
            $os_total = $statement_search_service->totalForDate($date);
            $this->info('Opensearch Total: ' . $statement_search_service->totalForDate($date));
            $source_diff = $db_diff - $os_total;
            $this->info('Source Difference: ' . $source_diff);
            $source_percentage = floor(($os_total / $db_diff) * 100);
            $this->info('Source Percentage: ' . $source_percentage . '%');
            $this->info('Source Difference DB Percentage: ' . floor(($source_diff / $db_diff) * 100) . '%');
            $this->info('Source Difference OS Percentage: ' . floor(($source_diff / $os_total) * 100) . '%');


        } else {
            $this->info('Could not find the first or last ids: ' . $first_id . ' :: ' . $last_id);
        }
    }
}
