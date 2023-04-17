<?php

namespace App\Console\Commands;

use App\Models\Statement;
use Illuminate\Console\Command;

class GenerateStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:statements {amount=1000}';

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
        Statement::factory()->count($this->argument('amount'))->create();
    }
}
