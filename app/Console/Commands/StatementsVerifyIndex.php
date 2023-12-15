<?php

namespace App\Console\Commands;

use App\Jobs\VerifyIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


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
    protected $description = 'Verify the opensearch index for date for an id range max - min';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $query_chunk = $this->argument('query_chunk') === 'default' ? 10000 : (int)$this->argument('query_chunk');
        $searchable_chunk = $this->argument('searchable_chunk') === 'default' ? 100 : (int)$this->argument('searchable_chunk');
        $min = $this->argument('min') === 'default' ? DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
        $max = $this->argument('max') === 'default' ? DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');

        Log::info('Index verification started for ids: ' . $max . ' :: ' . $min);
        Cache::forever('verify_jobs', 1);
        Cache::forever('verify_jobs_run', 1);
        Cache::forever('verify_jobs_diff', 0);
        VerifyIndex::dispatch($max, $min, $query_chunk, $searchable_chunk);
    }
}
