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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StatementDeDupulicateRange implements ShouldQueue
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
        $difference = $this->max - $this->min;
        // If the difference is small enough then do the searchable.
        if ($difference <= $this->chunk) {
            try {
                $statements = Statement::query()->where('id', '>=', $this->min)->where('id', '<=', $this->max)->get();
                $duplicated_statements = [];
                foreach ($statements as $statement) {
                    $key = 'puid-' . $statement->platform_id . "-" . trim($statement->puid);
                    if (Cache::get($key, false)) {
                        // Duplicate found
                        $duplicated_statements[] = [
                            'id' => $statement->id,
                            'platform_id' => $statement->platform_id,
                            'puid' => $statement->puid
                        ];
                    } else {
                        Cache::forever($key, true);
                    }
                }

                if (count($duplicated_statements)) {
                    Log::warning('DuplicatedIdsFound', $duplicated_statements);
                }

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
