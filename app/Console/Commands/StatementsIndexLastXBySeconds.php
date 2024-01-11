<?php

namespace App\Console\Commands;

use App\Jobs\StatementIndexBag;
use App\Jobs\StatementIndexSecond;
use App\Models\Statement;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StatementsIndexLastXBySeconds extends Command
{
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
        $start->subSeconds((int)$this->argument('seconds'));
        while($start <= $end) {
            StatementIndexSecond::dispatch($start->timestamp);
            $start->addSecond();
        }
    }
}
