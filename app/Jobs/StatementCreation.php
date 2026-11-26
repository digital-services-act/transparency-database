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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $when = 0, public bool $weird = false) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $factory = Statement::factory();

        if ($this->weird) {
            $factory = $factory->withWeirdText();
        }

        if ($this->when !== 0) {
            $factory->create([
                'created_at' => Carbon::createFromTimestamp($this->when),
                'updated_at' => Carbon::createFromTimestamp($this->when),
            ]);
        } else {
            $factory->create();
        }
    }
}
