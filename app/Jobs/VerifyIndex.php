<?php

namespace App\Jobs;

use App\Models\Statement;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenSearch\Client;

class VerifyIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $max, public int $min, public int $query_chunk, public int $searchable_chunk)
    {
    }

    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle(Client $client): void
    {
        $stop = Cache::get('stop_reindexing', false);
        if (!$stop) {
            // The ID spread
            $id_difference = $this->max - $this->min;

            // Is the spread small enough to do a decent query on?
            if ($id_difference <= $this->query_chunk) {

                try {
                    $db_count       = Statement::query()->where('id', '>=', $this->min)->where('id', '<=', $this->max)->count();
                    $opensearch_sql = "SELECT count(id) FROM statement_index WHERE id >= " . $this->min . " AND id <= " . $this->max;

                    $opensearch_count = $client->sql()->query([
                        'query' => $opensearch_sql,
                    ])['datarows'][0][0] ?? -1;

                    // How much were we off?
                    $off = $db_count - $opensearch_count;

                    // Did we have a difference?
                    if ($off > 0) {
                        // reindex it.
                        StatementIndexRange::dispatch($this->max, $this->min, $this->searchable_chunk);
                    }

                } catch (Exception $exception) {
                    Log::error($exception->getMessage());
                }

            } else {
                // Break into 2
                $this->breakItIntoTwo($id_difference);
            }
        }
    }

    /**
     * @return void
     */
    private function breakItIntoTwo(int $id_difference): void
    {
        $break = floor($id_difference / 2);
        self::dispatch($this->max, ($this->max - $break), $this->query_chunk, $this->searchable_chunk);
        self::dispatch(($this->max - $break - 1), $this->min, $this->query_chunk, $this->searchable_chunk);
    }
}
