<?php

namespace App\Console\Commands;

use App\Services\DayArchiveService;
use App\Services\StatementElasticSearchService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
    protected $signature = 'statements:elastic-date-total
        {date=yesterday}
        {--raw-count : Count statements_beta rows for the date directly from the database.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give stats for a date, using the elasticsearch index.';

    /**
     * Execute the console command.
     *
     * @throws Exception
     * @throws Throwable
     */
    public function handle(DayArchiveService $day_archive_service, StatementElasticSearchService $statement_elastic_search_service): void
    {
        $date = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');

        $first_id = $day_archive_service->getFirstIdOfDate($date);
        $last_id = $day_archive_service->getLastIdOfDate($date);

        if ($first_id && $last_id) {
            $this->info('Date: '.$date_string);
            $this->info('First ID: '.$first_id);
            $this->info('Last ID: '.$last_id);
            $db_diff = $last_id - $first_id;
            $this->info('Difference in IDs: '.$db_diff);
            $es_total = $statement_elastic_search_service->totalForDate($date);
            $this->info('Elastic Total: '.$es_total);
            $source_diff = $db_diff - $es_total;
            $this->info('Source Difference: '.$source_diff);
            $source_percentage = $db_diff > 0 ? floor(($es_total / $db_diff) * 100) : 0;
            $this->info('Source Percentage: '.$source_percentage.'%');
            $this->info('Source Difference DB Percentage: '.($db_diff > 0 ? floor(($source_diff / $db_diff) * 100).'%' : 'n/a'));
            $this->info('Source Difference ES Percentage: '.($es_total > 0 ? floor(($source_diff / $es_total) * 100).'%' : 'n/a'));

            if ((bool) $this->option('raw-count')) {
                $raw_count = $this->rawStatementCountForDate($date);
                $this->info('Raw DB Date Count: '.$raw_count);
                $this->info('Raw DB Date Count Difference From Elastic: '.($raw_count - $es_total));
                $this->info('Raw DB Date Count Difference From ID Span: '.($db_diff - $raw_count));
            }

            if ($db_diff !== $es_total) {
                $this->outputIdOverlapDiagnostics($day_archive_service, $date, (int) $first_id, (int) $last_id, $source_diff);
            }

            $this->info('statements:index-date '.$date_string);
            $totals = $statement_elastic_search_service->totalsForPlatformsDate($date);
            $methods = $statement_elastic_search_service->methodsByPlatformsDate($date);
            foreach ($totals as $index => $total) {
                $totals[$index]['API'] = $methods[$total['platform_id']]['API'] ?? 0;
                $totals[$index]['API_MULTI'] = $methods[$total['platform_id']]['API_MULTI'] ?? 0;
                $totals[$index]['FORM'] = $methods[$total['platform_id']]['FORM'] ?? 0;
                unset($totals[$index]['permutation']);
            }

            if ($totals !== []) {
                $this->table(array_keys($totals[0]), $totals);
            }

        } else {
            $this->info('Could not find the first or last ids: '.$first_id.' :: '.$last_id);
        }
    }

    private function rawStatementCountForDate(Carbon $date): int
    {
        $start_of_day = $date->copy()->startOfDay();

        return DB::table('statements_beta')
            ->where('created_at', '>=', $start_of_day)
            ->where('created_at', '<', $start_of_day->copy()->addDay())
            ->count();
    }

    private function outputIdOverlapDiagnostics(
        DayArchiveService $day_archive_service,
        Carbon $date,
        int $first_id,
        int $last_id,
        int $source_diff,
    ): void {
        $previous_date = $date->copy()->subDay();
        $next_date = $date->copy()->addDay();

        $previous_last_id = $day_archive_service->getLastIdOfDate($previous_date);
        $next_first_id = $day_archive_service->getFirstIdOfDate($next_date);

        $previous_overlap = $previous_last_id && $previous_last_id >= $first_id;
        $next_overlap = $next_first_id && $next_first_id <= $last_id;
        $previous_overlap_span = $previous_overlap ? $previous_last_id - $first_id + 1 : 0;
        $next_overlap_span = $next_overlap ? $last_id - $next_first_id + 1 : 0;
        $total_overlap_span = $previous_overlap_span + $next_overlap_span;
        $overlap_accounts_for_difference = $source_diff > 0 && $total_overlap_span >= $source_diff;
        $remaining_difference_after_overlap = $source_diff > 0 ? max(0, $source_diff - $total_overlap_span) : 0;

        $this->info('Previous Date: '.$previous_date->format('Y-m-d'));
        $this->info('Previous Last ID: '.($previous_last_id ?: 'n/a'));
        $this->info('Next Date: '.$next_date->format('Y-m-d'));
        $this->info('Next First ID: '.($next_first_id ?: 'n/a'));
        $this->info('Previous Day ID Overlap: '.($previous_overlap ? 'yes' : 'no'));
        $this->info('Previous Day ID Overlap Span: '.$previous_overlap_span);
        $this->info('Next Day ID Overlap: '.($next_overlap ? 'yes' : 'no'));
        $this->info('Next Day ID Overlap Span: '.$next_overlap_span);
        $this->info('Total Adjacent ID Overlap Span: '.$total_overlap_span);
        $this->info('ID Overlap Accounts For Difference: '.($overlap_accounts_for_difference ? 'yes' : 'no'));
        $this->info('Remaining Difference After ID Overlap: '.$remaining_difference_after_overlap);
    }
}
