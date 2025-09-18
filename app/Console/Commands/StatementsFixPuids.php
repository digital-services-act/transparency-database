<?php

namespace App\Console\Commands;

use App\Jobs\StatementFixPuidBatch;
use App\Models\Platform;
use App\Models\Statement;
use App\Models\StatementAlpha;
use App\Services\StatementSearchService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenSearch\Client as OpenSearch;

/**
 * @codeCoverageIgnore
**/
class StatementsFixPuids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:fix-puids {platform?} {--dry-run} {--batch=1000}';
    protected $description = 'Fix faulty PUIDs for a platform and record affected dates in Redis for later CSV regenration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = (int) $this->option('batch');

        collect(['statements', 'statements_beta'])->each(function ($table) use ($batchSize) {
            DB::connection('mysql::read')
                ->table('faulty_ids')
                ->whereNull('updated_db_at')
                ->whereNull('updated_os_at')
                ->where('source_table', $table)
                ->chunkById($batchSize, function ($batch, $nr) use ($table) {
                    StatementFixPuidBatch::dispatch($table, $batch->pluck('id')->toArray(), $nr)->onQueue('default');
                    Log::info("Dispatched batch {$nr} for {$table}");
                });
        });
    }
}
