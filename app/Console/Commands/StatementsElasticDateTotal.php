<?php

namespace App\Console\Commands;

use App\Services\DayArchiveService;
use App\Services\StatementElasticSearchService;
use Exception;
use Illuminate\Console\Command;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class StatementsElasticDateTotal extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:elastic-date-total {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give stats for a date, using the elasticsearch index.';

    /**
     * Execute the console command.
     * @throws Exception
     * @throws Throwable
     */
    public function handle(DayArchiveService $day_archive_service, StatementElasticSearchService $statement_elastic_search_service): void
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
            $es_total = $statement_elastic_search_service->totalForDate($date);
            $this->info('Elastic Total: ' . $es_total);
            $source_diff = $db_diff - $es_total;
            $this->info('Source Difference: ' . $source_diff);
            $source_percentage = floor(($es_total / $db_diff) * 100);
            $this->info('Source Percentage: ' . $source_percentage . '%');
            $this->info('Source Difference DB Percentage: ' . floor(($source_diff / $db_diff) * 100) . '%');
            $this->info('Source Difference ES Percentage: ' . floor(($source_diff / $os_total) * 100) . '%');
            $this->info('statements:index-date ' . $date_string);
            $totals = $statement_elastic_search_service->totalsForPlatformsDate($date);
            $methods = $statement_elastic_search_service->methodsByPlatformsDate($date);
            foreach ($totals as $index => $total) {
                $totals[$index]['API'] = $methods[$total['platform_id']]['API'] ?? 0;
                $totals[$index]['API_MULTI'] = $methods[$total['platform_id']]['API_MULTI'] ?? 0;
                $totals[$index]['FORM'] = $methods[$total['platform_id']]['FORM'] ?? 0;
                unset($totals[$index]['permutation']);
            }
            $this->table(array_keys($totals[0]), $totals);

        } else {
            $this->info('Could not find the first or last ids: ' . $first_id . ' :: ' . $last_id);
        }
    }
}
