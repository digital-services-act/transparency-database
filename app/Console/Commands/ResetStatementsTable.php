<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class ResetStatementsTable extends Command
{
    protected $signature = 'statements:reset';
    protected $description = 'Drop and recreate the Statements table';

    public function handle()
    {
        $tableName = 'statements';

        if (Schema::hasTable($tableName)) {
            $this->info('Dropping the Statements table...');
            Schema::dropIfExists($tableName);
            $this->info('Statements table dropped.');
        }

        $this->info('Running CreateStatementsTable migration...');
        Artisan::call('migrate:refresh', [
            '--path' => 'database/migrations/2023_01_13_082758_create_statements_table.php',
        ]);
        $this->info('Statements table created.');
    }
}
