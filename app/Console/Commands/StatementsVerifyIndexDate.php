<?php

namespace App\Console\Commands;

use App\Jobs\VerifyIndex;
use App\Models\Statement;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StatementsVerifyIndexDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:verify-index-date {date=default} {query_chunk=default} {searchable_chunk=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the opensearch index for date.';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $query_chunk      = $this->argument('query_chunk') === 'default' ? 100000 : (int)$this->argument('query_chunk');
        $searchable_chunk = $this->argument('searchable_chunk') === 'default' ? 1000 : (int)$this->argument('searchable_chunk');
        $date             = $this->argument('date') === 'default' ? Carbon::yesterday() : Carbon::createFromFormat('Y-m-d', $this->argument('date'));

        $min = $day_archive_service->getFirstIdOfDate($date);
        $max = $day_archive_service->getLastIdOfDate($date);

        if ($min && $max) {
            Log::info('Index verification started for date: ' . $date->format('Y-m-d') . ' :: ' . $max . ' :: ' . $min);
        } else {
            Log::warning('Not able to obtain the highest or lowest ID for the day: ' . $date->format('Y-m-d'));
            Log::warning('Will try the fall back query.');
            $min = Statement::query()->selectRaw('min(id) as min')
                                     ->where('created_at', '>=', $date->format('Y-m-d') . ' 00:00:00')
                                     ->where('created_at', '<=', $date->format('Y-m-d') . ' 00:30:00')
                                     ->first()->min;
            $max = Statement::query()->selectRaw('max(id) as max')
                                     ->where('created_at', '<=', $date->format('Y-m-d') . ' 23:59:59')
                                     ->where('created_at', '>=', $date->format('Y-m-d') . ' 23:29:29')
                                     ->first()->max;
            if ( ! $min || ! $max) {
                Log::warning('Not able to obtain the highest or lowest ID for the day even with the fallback query: ' . $date->format('Y-m-d'));
                return;
            }
        }
        Cache::forever('verify_jobs', 1);
        Cache::forever('verify_jobs_run', 1);
        Cache::forever('verify_jobs_diff', 0);
        Cache::forever('verify_verified', 0);
        VerifyIndex::dispatch($max, $min, $query_chunk, $searchable_chunk);
    }
}
