<?php

namespace App\Jobs;

use App\Models\Statement;
use App\Services\StatementSearchService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 
 * @codeCoverageIgnore
 */
class StatementIndexBag implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct(public array $statement_ids)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        // Set this in cache, to emergency stop reindexing.
        $stop = Cache::get('stop_reindexing', false);

        if ( ! $stop) {
            $statements = Statement::query()->whereIn('id', $this->statement_ids)->get();
            try {
                $statement_search_service->bulkIndexStatements($statements);
            } catch (Exception $e) {
                Log::error('Indexing Error', ['exception' => $e]);
                self::dispatch($this->statement_ids);
            }
        }
    }
}
