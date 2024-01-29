<?php

namespace App\Console\Commands;

use App\Jobs\VerifyIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatementsVerifyIndex extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:verify-index {max=default} {min=default} {query_chunk=50000} {searchable_chunk=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the opensearch index for an id range max - min';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $query_chunk = $this->intifyArgument('query_chunk');
        $searchable_chunk = $this->intifyArgument('searchable_chunk');
        $min = $this->argument('min') === 'default' ? DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
        $max = $this->argument('max') === 'default' ? DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');

        Log::info('Index verification started for ids: ' . $max . ' :: ' . $min);
        VerifyIndex::dispatch($max, $min, $query_chunk, $searchable_chunk);
    }
}
