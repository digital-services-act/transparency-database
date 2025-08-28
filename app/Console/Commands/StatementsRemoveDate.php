<?php

namespace App\Console\Commands;

use App\Jobs\PlatformPuidDeleteChunk;
use App\Jobs\StatementDeleteChunk;
use App\Services\DayArchiveService;
use App\Services\StatementElasticSearchService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
 */
class StatementsRemoveDate extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:remove-date {date=181} {chunk=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove (delete) statements for a day';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service, StatementElasticSearchService $statement_elastic_search_service): void
    {

        $chunk = $this->intifyArgument('chunk');
        $date = $this->sanitizeDateArgument();

        $min = $day_archive_service->getFirstIdOfDate($date);
        $max = $day_archive_service->getLastIdOfDate($date);

        // Delete the sors in the elastic, this is based on received_date
        $statement_elastic_search_service->deleteStatementsForDate($date);

        if ($min && $max) {
            Log::info('Statement Removing Started', ['date' => $date->format('Y-m-d'), 'at' => Carbon::now()->format('Y-m-d H:i:s')]);
            StatementDeleteChunk::dispatch($min, $max, $chunk);
        }

        // Now remove PlatformPuids too
        $ppmin = $day_archive_service->getFirstPlatformPuidIdOfDate($date);
        $ppmax = $day_archive_service->getLastPlatformPuidIdOfDate($date);
        if ($ppmin && $ppmax) {
            Log::info('PlatformPuid Removing Started', ['date' => $date->format('Y-m-d'), 'at' => Carbon::now()->format('Y-m-d H:i:s')]);
            PlatformPuidDeleteChunk::dispatch($ppmin, $ppmax, $chunk);
        }

        if (! $min || ! $max) {
            Log::warning('Not able to obtain the highest or lowest ID for the day: '.$date->format('Y-m-d'));
        }

        if (! $ppmin || ! $ppmax) {
            Log::warning('Not able to obtain the highest or lowest PlatformPuid ID for the day: '.$date->format('Y-m-d'));
        }
    }
}
