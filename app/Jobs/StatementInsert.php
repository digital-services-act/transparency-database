<?php

namespace App\Jobs;

use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class StatementInsert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $payload;


    /**
     * Create a new job instance.
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
        $key = 'queued|' . $this->payload['platform_id'] . '|' . $this->payload['puid'];
        Cache::put($key, $payload);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $key = 'queued|' . $this->payload['platform_id'] . '|' . $this->payload['puid'];
        Statement::create($this->payload);
        Cache::delete($key);
    }
}
