<?php

namespace App\Console\Commands;

use App\Jobs\StatementDeDupulicateRange;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StatementsDeDuplicateDate extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:deduplicate-date {date=yesterday} {chunk=10000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index statements for a day';

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
            StatementDeDupulicateRange::dispatch($max, $min, $chunk)->onQueue('dedupe');
        } else {
            Log::warning('Not able to obtain the highest or lowest ID for the day: ' . $date->format('Y-m-d'));
        }
    }
}
