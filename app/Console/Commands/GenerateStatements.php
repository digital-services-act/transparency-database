<?php

namespace App\Console\Commands;

use App\Jobs\SpamStatementCreation;
use App\Jobs\StatementCreation;
use App\Models\Statement;
use Illuminate\Console\Command;

class GenerateStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:generate {amount=1000} ';

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
        for ($cpt = 0; $cpt < $this->argument('amount'); $cpt++) {
            StatementCreation::dispatch();
        }
    }
}
