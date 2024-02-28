<?php

namespace App\Console\Commands;

use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Throwable;

class StatementsDateTotal extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:date-total {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a day archive compile jobs.';

    /**
     * Execute the console command.
     * @throws Exception
     * @throws Throwable
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $date        = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');

        $first_id = $day_archive_service->getFirstIdOfDate($date);
        $last_id  = $day_archive_service->getLastIdOfDate($date);

        $this->info('Date: ' . $date_string);
        $this->info('First ID: ' . $last_id);
        $this->info('Last ID: ' . $last_id);
        $this->info('Difference in IDs: ' . $last_id - $first_id);
    }
}
