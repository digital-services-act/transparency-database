<?php

namespace App\Console\Commands;

use App\Jobs\StatementIndexSecond;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class StatementsIndexDateBySeconds extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:index-date-by-seconds {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index statements for a day by indexing seconds';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $date = $this->sanitizeDateArgument();
        $start = $date->clone();
        $start->subSeconds($start->secondsSinceMidnight());

        $end = $date->clone();
        $end->addSeconds($end->secondsUntilEndOfDay());

        while($start <= $end) {
            StatementIndexSecond::dispatch($start->timestamp);
            $start->addSecond();
        }
    }
}
