<?php

namespace App\Console\Commands;

use App\Jobs\StatementSearchableChunk;
use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
 */
class StatementsIndexRangeSeq extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:index-range-seq {min=default} {max=default} {chunk=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index the Statements based on a range in a sequential way.';

    /**
     * Execute the console command.
     */
    public function handle(StatementSearchService $statement_search_service): void
    {

        $chunk = $this->intifyArgument('chunk');

        if ($this->argument('min') === 'opensearch') {
            $min = $statement_search_service->highestId();
        } else {
            $min = $this->argument('min') === 'default' ? DB::table('statements_beta')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
        }

        $max = $this->argument('max') === 'default' ? DB::table('statements_beta')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');

        if ($min && $max && $min < $max) {
            Log::info('Indexing started for range: ' . $min . ' :: ' . $max . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
            StatementSearchableChunk::dispatch($min, $max, $chunk);
        }
    }
}
