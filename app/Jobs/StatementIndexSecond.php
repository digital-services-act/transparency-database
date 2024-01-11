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

class StatementIndexSecond implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timestamp;


    /**
     * Create a new job instance.
     */
    public function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Execute the job.
     *
     * @param StatementSearchService $statement_search_service
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        // Set this in cache, to emergency stop reindexing.
        $stop = Cache::get('stop_reindexing', false);

        if ( ! $stop) {
            $statements = Statement::query()->where('created_at', date("Y-m-d H:i:s", $this->timestamp))->get();
            try {
                $statement_search_service->bulkIndexStatements($statements);
            } catch (Exception $e) {
                Log::error('Indexing Error: ' . $e->getMessage());
                Log::error('Trying again!');
                self::dispatch($this->timestamp);
            }
        }
    }
}
