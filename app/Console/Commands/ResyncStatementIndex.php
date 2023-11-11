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
        $chunk = 100;
        $start = DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min;
        $end = DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max;

        while($end >= $start && $end > 0)
        {
            StatementSearchableChunk::dispatch($end - $chunk, $end);
            $end -= ($chunk + 1);
        }
    }
}
