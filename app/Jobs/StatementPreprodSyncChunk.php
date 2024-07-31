<?php

namespace App\Jobs;

use App\Models\Statement;
use App\Services\StatementSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;

class   StatementPreprodSyncChunk implements ShouldQueue
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
     * @throws JsonException
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        // Set this in cache, to emergency stop reindexing.
        $stop = Cache::get('stop_reindexing', false);
        if (!$stop) {
            $end = $this->min + $this->chunk;

            if ($end > $this->max) {
                $end = $this->max;
            }


            // Dispatch the next one
            if ($end < $this->max) {
                $next_min = $this->min + $this->chunk + 1;
                // Start the next one.
                self::dispatch($next_min, $this->max, $this->chunk);
            }

            $range = range($this->min, $end);
            // Bulk sync

            $to_insert = DB::connection('mysql::read')->table('statements')->whereIn('id', $range)->get()->map(function ($item){
                return get_object_vars($item);
            })->toArray();
            Statement::on('mysqlpreprod')->insert($to_insert);

            if ($end >= $this->max) {
                Log::info('StatementPreprodSyncChunk Max Reached at ' . Carbon::now()->format('Y-m-d H:i:s'));
            }
        }
    }
}
