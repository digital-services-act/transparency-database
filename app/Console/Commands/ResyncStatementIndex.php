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
    protected $signature = 'statements:resync-index {min=default} {max=default}';

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
        $statuses = 1000000;
        $chunk = 100;

        $min = $this->argument('min') === 'default' ? DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
        $max = $this->argument('max') === 'default' ? DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');
        
        StatementSearchableChunk::dispatch($max, $chunk, $min, $statuses, true);
    }
}
