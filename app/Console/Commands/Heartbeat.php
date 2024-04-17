<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Heartbeat extends Command
{

    protected $signature = 'heartbeat';

    protected $description = 'Check if the database is up by querying the users table.';

    public function handle()
    {
        try {
            // Attempt to query the users table
            DB::table('users')->select(DB::raw(1))->take(1)->get();

            // If the query is successful, database is up
            $this->info('Database is up.');

        } catch (\Exception $e) {
            // If an exception occurs, database is down
            $this->error('Database is down: ' . $e->getMessage());
        }
    }
}
