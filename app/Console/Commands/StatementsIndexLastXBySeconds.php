<?php

namespace App\Console\Commands;

use App\Jobs\StatementIndexSecond;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class StatementsIndexLastXBySeconds extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:index-last-x-by-seconds {seconds=65}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index statements for the last X seconds by second.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $end = Carbon::now();
        $start = $end->clone();
        $seconds = $this->intifyArgument('seconds');

        $start->subSeconds($seconds);
        while($start <= $end) {
            StatementIndexSecond::dispatch($start->timestamp);
            $start->addSecond();
        }
    }
}
