<?php

namespace App\Console\Commands;

use App\Jobs\VerifyIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class StatementsVerifyIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:verify-index {max=default} {min=default} {query_chunk=default} {searchable_chunk=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify and fix the Opensearch Statements Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $query_chunk = $this->argument('query_chunk') === 'default' ? 1000000 : (int)$this->argument('query_chunk');
        $searchable_chunk = $this->argument('searchable_chunk') === 'default' ? 500 : (int)$this->argument('searchable_chunk');
        $min = $this->argument('min') === 'default' ? DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
        $max = $this->argument('max') === 'default' ? DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');
        VerifyIndex::dispatch($max, $min, $query_chunk, $searchable_chunk);
    }
}
