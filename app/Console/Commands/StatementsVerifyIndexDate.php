<?php

namespace App\Console\Commands;

use App\Jobs\VerifyIndex;
use App\Models\Statement;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StatementsVerifyIndexDate extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:verify-index-date {date=yesterday} {query_chunk=50000} {searchable_chunk=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the opensearch index for date.';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $query_chunk      = $this->intifyArgument('query_chunk');
        $searchable_chunk = $this->intifyArgument('searchable_chunk');
        $date             = $this->sanitizeDateArgument();

        $min = $day_archive_service->getFirstIdOfDate($date);
        $max = $day_archive_service->getLastIdOfDate($date);

        if ($min && $max) {
            Log::info('Index verification started for date: ' . $date->format('Y-m-d') . ' :: ' . $max . ' :: ' . $min);
        } else {
            Log::error('Not able to obtain the highest or lowest ID for the day: ' . $date->format('Y-m-d'));
        }

        VerifyIndex::dispatch($max, $min, $query_chunk, $searchable_chunk);
    }
}
