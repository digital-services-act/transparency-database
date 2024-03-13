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
    public function __construct(public int $max, public int $min, public int $chunk)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(Client $client): void
    {
        $difference = $this->max - $this->min;
        // If the difference is small enough then do the searchable.
        if ($difference <= $this->chunk) {
            try {
                $statements = DB::connection('mysql::read')->table('statements')
                                ->select('id', 'uuid', 'platform_id', 'puid', 'created_at')
                                ->where('id', '>=', $this->min)
                                ->where('id', '<=', $this->max)
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
                    Storage::put('duplicated-' . $count . '-' . $this->min . '-' . $this->max . '.json', json_encode($duplicated_statements, JSON_THROW_ON_ERROR));
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
            } catch (Exception $e) {
                // Do it again
                Log::error('Deduplication Error', ['exception' => $e]);
                self::dispatch($this->max, $this->min, $this->chunk)->onQueue('dedupe');
            }
        } else {
            // The difference was too big, split it in half and dispatch those jobs.
            $break = ceil($difference / 2);
            self::dispatch($this->max, ($this->max - $break), $this->chunk)->onQueue('dedupe'); // first half
            self::dispatch(($this->max - $break - 1), $this->min, $this->chunk)->onQueue('dedupe'); // second half
        }
    }
}
