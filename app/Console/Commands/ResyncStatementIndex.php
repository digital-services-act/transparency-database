<?php

namespace App\Console\Commands;

use App\Jobs\StatementSearchableChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class ResyncStatementIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:resync-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resync the Opensearch Statements Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
//        if (env('SCOUT_DRIVER', '') !== 'opensearch') {
//            $this->error('opensearch is not the SCOUT_DRIVER');
//
//            return;
//        }

        $chunk = 2000;
        $start = DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min;
        $max = DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max;

        while($start <= $max)
        {
            if ($this->option('verbose')) {
                $this->info('Start: '. $start);
            }
            StatementSearchableChunk::dispatch($start, $start + $chunk);
            $start += $chunk;

        }
    }
}
