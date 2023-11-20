<?php

namespace App\Jobs;

use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StatementSearchableChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $start;
    public int $chunk;
    public int $min;
    public int $statuses;

    /**
     * Create a new job instance.
     */
    public function __construct(int $start, int $chunk, int $min, int $statuses)
    {
        $this->start = $start;
        $this->min = $min;
        $this->chunk = $chunk;
        $this->statuses = $statuses;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('reindexing')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $end = $this->start - $this->chunk;
        $range = range($this->start, $end);
        foreach ($range as $id) {
            if ($id % $this->statuses === 0) {
                Log::debug('Reindexing: ' . $id);
            }
        }
        if ($end > $this->min) {
            $next_start = $this->start - $this->chunk - 1;
            self::dispatch($next_start, $this->chunk, $this->min, $this->statuses);
        }
        Statement::query()->whereIn('id', $range)->searchable();
    }
}
