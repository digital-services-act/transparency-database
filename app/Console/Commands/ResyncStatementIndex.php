<?php

namespace App\Console\Commands;

use App\Jobs\StatementSearchableChunk;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use OpenSearch\Client;
use Spatie\Permission\Models\Role;
use Zing\LaravelScout\OpenSearch\Engines\OpenSearchEngine;

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
        if (env('SCOUT_DRIVER', '') !== 'opensearch') {
            $this->error('opensearch is not the SCOUT_DRIVER');

            return;
        }

        $chunk = 1000;
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
