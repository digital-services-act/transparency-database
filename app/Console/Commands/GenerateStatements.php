<?php

namespace App\Console\Commands;

use App\Jobs\StatementCreation;
use Illuminate\Console\Command;

class GenerateStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:generate {amount=200} {--now}';

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
        for ($cpt = 0; $cpt < (int)$this->argument('amount'); $cpt++) {
            StatementCreation::dispatch($this->option('now'));
        }
    }
}
