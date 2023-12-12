<?php

namespace App\Console\Commands;

use App\Jobs\StatementCreation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:generate {amount=200} {--now} {--sod} {--eod}';

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
        $created_at = 0;
        if ($this->option('now')) {
            $created_at = Carbon::now()->timestamp;
        }

        if ($this->option('sod')) {
            $created_at = Carbon::now();
            $created_at = $created_at->subSeconds($created_at->secondsSinceMidnight())->timestamp;
        }

        if ($this->option('eod')) {
            $created_at = Carbon::now();
            $created_at = $created_at->addDay()->subSeconds($created_at->secondsSinceMidnight())->subSecond()->timestamp;
        }

        for ($cpt = 0; $cpt < (int)$this->argument('amount'); $cpt++) {
            StatementCreation::dispatch($created_at);
        }
    }
}
