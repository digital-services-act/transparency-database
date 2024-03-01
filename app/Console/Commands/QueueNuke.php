<?php

namespace App\Console\Commands;



use Illuminate\Console\Command;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class QueueNuke extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:nuke';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all queue tables and restart queue';


    public function handle(): void
    {
        DB::table('jobs')->truncate();
        DB::table('failed_jobs')->truncate();
        DB::table('job_batches')->truncate();
        $this->call('queue:restart');
    }
}
