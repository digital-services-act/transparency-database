<?php

namespace App\Jobs;

use App\Services\StatementArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StatementArchiveRange implements ShouldQueue
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
    public function handle(StatementArchiveService $statement_archive_service): void
    {

        $end = $this->min + $this->chunk;

        if ($end > $this->max ) {
            $end = $this->max;
        }

        // Dispatch the next one
        if ($end < $this->max) {
            $next_min = $this->min + $this->chunk + 1;
            // Start the next one.
            self::dispatch($next_min, $this->max, $this->chunk);
        }

        $range = range($this->min, $end);

        $statement_archive_service->archiveStatementsFromIds($range);

    }
}
