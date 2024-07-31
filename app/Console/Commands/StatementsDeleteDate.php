<?php

namespace App\Console\Commands;

use App\Jobs\StatementDeleteSecond;
use App\Jobs\StatementIndexRange;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StatementsDeleteDate extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:delete-date {date=211}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete statements for a day';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $date = $this->sanitizeDateArgument();


        $min = $date->subSeconds($date->secondsSinceMidnight())->clone();
        $max = $date->addSeconds($date->secondsUntilEndOfDay())->clone();


        while($min <= $max) {
            StatementDeleteSecond::dispatch($min->timestamp);
            $min->addSecond();
        }
    }
}
