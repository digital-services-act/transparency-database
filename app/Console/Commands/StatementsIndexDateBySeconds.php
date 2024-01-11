<?php

namespace App\Console\Commands;

use App\Jobs\StatementIndexSecond;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class StatementsIndexDateBySeconds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:index-date-by-seconds {date=default}';

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

        $date_in = $this->argument('date');
        $date = $this->argument('date') === 'default' ? Carbon::yesterday() : Carbon::createFromFormat('Y-m-d', $this->argument('date'));

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
