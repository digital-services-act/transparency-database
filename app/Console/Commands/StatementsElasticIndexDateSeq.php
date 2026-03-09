<?php

namespace App\Console\Commands;

use App\Jobs\StatementElasticSearchableChunk;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
 */
class StatementsElasticIndexDateSeq extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:elastic-index-date-seq {date=yesterday} {chunk=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elastic Index statements for a day';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        Log::info('Step 1');
        $chunk = $this->intifyArgument('chunk');
        Log::info('Step 2');
        $date = $this->sanitizeDateArgument();

        Log::info('Step 3');
        $min = $day_archive_service->getFirstIdOfDate($date);
        Log::info('Step 4');
        $max = $day_archive_service->getLastIdOfDate($date);

        if ($min && $max) {
            Log::info('Step 5');
            Log::info('Indexing started for date: '.$date->format('Y-m-d').' at '.Carbon::now()->format('Y-m-d H:i:s'));
            StatementElasticSearchableChunk::dispatch($min, $max, $chunk);
        } else {
            Log::info('Step 6');
            Log::warning('Not able to obtain the highest or lowest ID for the day: '.$date->format('Y-m-d'));
        }
    }
}
