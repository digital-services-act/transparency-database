<?php

namespace App\Jobs;

use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class StatementCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $when;
    /**
     * Create a new job instance.
     */
    public function __construct(int $when = 0)
    {
        $this->when = $when;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->when) {
            Statement::factory()->create([
                'created_at' => Carbon::createFromTimestamp($this->when),
                'updated_at' => Carbon::createFromTimestamp($this->when),
            ]);
        } else {
            Statement::factory()->create();
        }
    }
}
