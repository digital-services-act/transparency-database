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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct(public int $max, public int $min, public int $chunk)
    {
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
                    $range = range($this->min, $this->max);
                    $statements = Statement::on('mysql::read')->whereIn('id', $range)->get();
                    $statement_search_service->bulkIndexStatements($statements);
                } catch (Exception $e) {
                    // Do it again
                    Log::error('Indexing Error', ['exception' => $e]);
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
