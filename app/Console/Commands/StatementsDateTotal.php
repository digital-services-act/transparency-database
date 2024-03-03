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
            $this->info('Difference in IDs: ' . $last_id - $first_id);
            $this->info('Opensearch Total: ' . $statement_search_service->totalForDate($date));

            $this->info('Calculating the DB total....');

            $chunk = 100000;
            $total = 0;
            while ($first_id <= $last_id) {
                $till = min($last_id, $first_id + $chunk);
                $total += DB::connection('mysql::read')->table('statements')->selectRaw('count(*) as total')->where('id', '>=' , $first_id)->where('id', '<=', $till)->first()->total;
                $first_id += $chunk + 1;
            }

            $this->info('Total from DB: ' . $total);

        } else {
            $this->info('Could not find the first or last ids: ' . $first_id . ' :: ' . $last_id);
        }
    }
}
