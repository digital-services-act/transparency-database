<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class ResetStatementsTable extends Command
{
    protected $signature = 'statements:reset {--force} {--reallyforce}';
    protected $description = 'Drop and recreate the Statements table';

    public function handle()
    {
        if (config('app.env') !== 'production' || ($this->option('force') && $this->option('reallyforce'))) {
            $tableName = 'statements';

            if (Schema::hasTable($tableName)) {
                $this->info('Dropping the Statements table...');
                Schema::dropIfExists($tableName);
                $this->info('Statements table dropped.');
            }

            $this->info('Running CreateStatementsTable migration...');
            Artisan::call('migrate:refresh', [
                '--path'  => 'database/migrations/2023_01_13_082758_create_statements_table.php',
                '--force' => true
            ]);
            $this->info('Statements table created.');
        } else {
            $this->error('Oh hell no!');
            $this->error('We do not run this in production.');
            $this->error('I might do it if you use the force.');
            $this->error('Even then, you are going to have to really force it.');
        }
    }
}
