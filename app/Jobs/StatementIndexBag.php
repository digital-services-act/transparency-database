<?php

namespace App\Jobs;

use App\Models\Statement;
use App\Services\StatementSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use JsonException;

class StatementIndexBag implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $statement_ids;


    /**
     * Create a new job instance.
     */
    public function __construct($statement_ids)
    {
        $this->statement_ids = $statement_ids;
    }

    /**
     * Execute the job.
     * @throws JsonException
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        // Set this in cache, to emergency stop reindexing.
        $stop = Cache::get('stop_reindexing', false);

        if ( ! $stop) {
            $statements = Statement::query()->whereIn('id', $this->statement_ids)->get();
            $statement_search_service->bulkIndexStatements($statements);
        }
    }
}
