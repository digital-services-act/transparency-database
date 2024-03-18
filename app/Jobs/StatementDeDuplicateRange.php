<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenSearch\Client;

class StatementDeDuplicateRange implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct(public int $min, public int $max, public int $chunk)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(Client $client): void
    {

        $end = $this->min + $this->chunk;

        if ($end > $this->max ) {
            $end = $this->max;
        }

        // Dispatch the next one
        if ($end < $this->max) {
            $next_min = $this->min + $this->chunk + 1;
            // Start the next one.
            self::dispatch($next_min, $this->max, $this->chunk)->onQueue('dedupe');
        }

        $range = range($this->min, $end);
        $statements = DB::connection('mysql::read')->table('statements')
                        ->select('id', 'uuid', 'platform_id', 'puid', 'created_at')
                        ->whereIn('id', $range)
                        ->get();

        $duplicated_statements = [];
        $ids_to_delete = [];
        $opensearch_bulk_delete = [];

        foreach ($statements as $statement) {
            $key = 'puid-' . $statement->platform_id . "-" . $statement->puid;
            if (Cache::store('redis')->has($key)) {
                // Duplicate found
                $duplicated_statements[] = [
                    'id' => $statement->id,
                    'platform_id' => $statement->platform_id,
                    'puid' => $statement->puid
                ];
                $ids_to_delete[] = $statement->id;
                $opensearch_bulk_delete[] = json_encode([
                    'delete' => [
                        '_index' => 'statement_index',
                        '_id'    => $statement->id
                    ]
                ], JSON_THROW_ON_ERROR);
            } else {
                Cache::store('redis')->forever($key, 1);
            }
        }

        $count = count($duplicated_statements);
        if ($count) {
            Storage::put('duplicated-' . $count . '-' . $this->min . '-' . $end . '.json', json_encode($duplicated_statements, JSON_THROW_ON_ERROR));
//                    try {
//                        // Delete the ids from the opensearch
//                        $client->bulk(['require_alias' => true, 'body' => implode("\n", $opensearch_bulk_delete)]);
//
//                        // Delete From the DB
//                        DB::table('statements')->whereIn('id', $ids_to_delete)->delete();
//                    } catch (Exception $e) {
//                        Log::error('DD Error: ' . $e->getMessage(), $e->getTrace());
//                    }
        }
    }
}
