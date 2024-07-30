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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatementDeleteSecond implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(public int $timestamp)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::table('statements')->where('created_at', date('Y-m-d H:i:s', $this->timestamp))->delete();
    }
}
