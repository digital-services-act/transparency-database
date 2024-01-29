<?php

namespace App\Console\Commands;

use App\Jobs\StatementCreation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateStatements extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:generate {amount=200} {date=today} {--sod} {--eod}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create X Statements';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $date = $this->sanitizeDateArgument();
        $amount = $this->intifyArgument('amount');

        if ($this->option('sod')) {
            $date->subSeconds($date->secondsSinceMidnight());
        }

        if ($this->option('eod')) {
            $date->addSeconds($date->secondsUntilEndOfDay());
        }

        for ($cpt = 0; $cpt < $amount; $cpt++) {
            StatementCreation::dispatch($date);
        }
    }
}
