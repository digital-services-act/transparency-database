<?php

namespace App\Console\Commands;

use App\Jobs\StatementSearchableChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StatementsResyncIndex extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:resync-index {max=default} {min=default} {chunk=500}';

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
        $chunk = $this->intifyArgument('chunk');

        $min = $this->argument('min') === 'default' ? DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
        $max = $this->argument('max') === 'default' ? DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');

        StatementSearchableChunk::dispatch($max, $chunk, $min, $statuses, true);
    }
}
