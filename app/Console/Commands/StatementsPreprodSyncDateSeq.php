<?php

namespace App\Console\Commands;

use App\Jobs\StatementPreprodSyncChunk;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
 */
class StatementsPreprodSyncDateSeq extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:preprod-sync-date-seq {date=yesterday} {chunk=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync statements for a day to the preprod DB';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $chunk = $this->intifyArgument('chunk');
        $date = $this->sanitizeDateArgument();

        $min = $day_archive_service->getFirstIdOfDate($date);
        $max = $day_archive_service->getLastIdOfDate($date);

        if ($min && $max) {
            Log::info('Syncing started for date: ' . $date->format('Y-m-d') . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
            StatementPreprodSyncChunk::dispatch($min, $max, $chunk);
        } else {
            Log::warning('Not able to obtain the highest or lowest ID for the day: ' . $date->format('Y-m-d'));
        }
    }
}
