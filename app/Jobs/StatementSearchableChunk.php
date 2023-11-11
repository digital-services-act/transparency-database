<?php

namespace App\Jobs;

use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StatementSearchableChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $start;
    public int $chunk;
    public int $min;

    /**
     * Create a new job instance.
     */
    public function __construct(int $start, int $chunk, int $min)
    {
        $this->start = $start;
        $this->min = $min;
        $this->chunk = $chunk;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $end = $this->start - $this->chunk;
        $range = range($this->start, $end);
        if ($end > $this->min) {
            $next_start = $this->start - $this->chunk - 1;
            self::dispatch($next_start, $this->chunk, $this->min);
        }
        Statement::query()->whereIn('id', $range)->searchable();
    }
}
