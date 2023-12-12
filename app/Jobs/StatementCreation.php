<?php

namespace App\Jobs;

use App\Models\Statement;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StatementCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $now;
    /**
     * Create a new job instance.
     */
    public function __construct(bool $now = false)
    {
        $this->now = $now;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->now) {
            Statement::factory()->create([
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } else {
            Statement::factory()->create();
        }

    }
}
