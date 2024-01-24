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

class StatementIndexRange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $min;
    public int $max;
    public int $chunk;

    /**
     * Create a new job instance.
     */
    public function __construct(int $max, int $min, int $chunk)
    {
        $this->min = $min;
        $this->max = $max;
        $this->chunk = $chunk;
    }

    /**
     * Execute the job.
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        // Set this in cache, to emergency stop reindexing.
        $stop = Cache::get('stop_reindexing', false);
        if (!$stop) {

            $difference = $this->max - $this->min;
            // If the difference is small enough then do the searchable.
            if ($difference <= $this->chunk) {

                try {
                    $statements = Statement::query()->where('id', '>=', $this->min)->where('id', '<=', $this->max)->get();
                    $statement_search_service->bulkIndexStatements($statements);
                } catch (Exception $e) {
                    // Do it again
                    Log::error('Indexing Error: ' . $e->getMessage());
                    Log::error('Trying again!');
                    self::dispatch($this->max, $this->min, $this->chunk);
                }
            } else {
                // The difference was too big, split it in half and dispatch those jobs.
                $break = ceil($difference / 2);
                self::dispatch($this->max, ($this->max - $break), $this->chunk); // first half
                self::dispatch(($this->max - $break - 1), $this->min, $this->chunk); // second half
            }
        }
    }
}
