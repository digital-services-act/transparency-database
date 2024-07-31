<?php

namespace App\Console\Commands;

use App\Jobs\StatementDeleteDay;
use Illuminate\Console\Command;

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
        StatementDeleteDay::dispatch($min->timestamp);
    }
}
