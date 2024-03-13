<?php

namespace App\Jobs;

use App\Models\Statement;
use App\Services\StatementSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class StatementSearchableChunk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct(public int $start, public int $chunk, public int $max, public bool $next = true)
    {
    }

    /**
     * Execute the job.
     * @throws JsonException
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        $end = $this->start + $this->chunk;

        if ($end > $this->max ) {
            $end = $this->max;
        }

        $range = range($this->start, $end);

        if ($this->next) {
            // Dispatch the next one
            if ($end < $this->max) {
                $next_start = $this->start + $this->chunk + 1;
                // Start the next one.
                self::dispatch($next_start, $this->chunk, $this->max);
            }
            // redo this one but with no next call.
            self::dispatch($this->start, $this->chunk, $this->max, false);
        } else {
            // Bulk indexing.
            $statements = Statement::on('mysql::read')->query()->whereIn('id', $range)->get();
            $statement_search_service->bulkIndexStatements($statements);
        }
    }
}
