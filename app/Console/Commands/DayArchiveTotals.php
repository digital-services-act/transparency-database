<?php

namespace App\Console\Commands;

use App\Models\DayArchive;
use App\Services\StatementSearchService;
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
    protected $description = 'Create archives entries for platforms and exports.';

    /**
     * Execute the console command.
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        $date = $this->sanitizeDateArgument();
        $day_archives = DayArchive::query()->whereDate('date', $date)->get();

        foreach ($day_archives as $day_archive) {

            $total = $day_archive->platform ? $statement_search_service->totalForPlatformDate($day_archive->platform, $day_archive->date) : $statement_search_service->totalForDate($day_archive->date);
            $this->info($day_archive->date . ' :: ' . ($day_archive->platform->name ?? 'Global')  . ' :: ' . $total);
            $day_archive->total = $total;
            if (!$this->option('nosave')) {
                $day_archive->save();
            }
        }
    }
}
