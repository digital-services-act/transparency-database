<?php

namespace App\Console\Commands;

use App\Jobs\StatementIndexRange;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StatementsIndexDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:index-date {date=default} {chunk=default}';

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
        $chunk = $this->argument('chunk') === 'default' ? 500 : (int)$this->argument('chunk');
        $date_in = $this->argument('date');
        $date = $this->argument('date') === 'default' ? Carbon::yesterday() : Carbon::createFromFormat('Y-m-d', $this->argument('date'));

        $min = $day_archive_service->getFirstIdOfDate($date);
        $max = $day_archive_service->getLastIdOfDate($date);

        if ($min && $max) {
            StatementIndexRange::dispatch($max, $min, $chunk);
        } else {
            Log::warning('Not able to obtain the highest or lowest ID for the day: ' . $date->format('Y-m-d'));
        }
    }
}
