<?php

namespace App\Console\Commands;

use App\Jobs\StatementArchiveRange;
use App\Services\DayArchiveService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StatementsArchiveDate extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:archive-date {date=180} {chunk=3000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive statements for a day';

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
            Log::info('Statement Archiving Started', ['date' => $date->format('Y-m-d'), 'at' => Carbon::now()->format('Y-m-d H:i:s')]);
            StatementArchiveRange::dispatch($min, $max, $chunk);
        } else {
            Log::warning('Not able to obtain the highest or lowest ID for the day: ' . $date->format('Y-m-d'));
        }
    }
}
