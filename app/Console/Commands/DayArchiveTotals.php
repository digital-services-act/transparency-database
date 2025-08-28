<?php

namespace App\Console\Commands;

use App\Models\DayArchive;
use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class DayArchiveTotals extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dayarchive:totals {date=yesterday} {--nosave}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the totals for day archives for a given date.';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $statement_elastic_search_service): void
    {
        $date = $this->sanitizeDateArgument();
        $day_archives = DayArchive::query()->whereDate('date', $date)->get();

        foreach ($day_archives as $day_archive) {

            $total = $day_archive->platform ? $statement_elastic_search_service->totalForPlatformDate($day_archive->platform, $day_archive->date) : $statement_elastic_search_service->totalForDate($day_archive->date);
            $this->info($day_archive->date.' :: '.($day_archive->platform->name ?? 'Global').' :: '.$total);
            $day_archive->total = $total;
            if (! $this->option('nosave')) {
                $day_archive->save();
            }
        }
    }
}
