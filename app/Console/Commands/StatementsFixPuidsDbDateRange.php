<?php

namespace App\Console\Commands;

use App\Jobs\StatementFixPuidDbIdChunk;
use App\Models\Platform;
use Carbon\Carbon;
use OpenSearch\Client as OpenSearch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * @codeCoverageIgnore
**/
class StatementsFixPuidsDbDateRange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:fix-puids-db-date-range
                            {start_date : Start date (YYYY-MM-DD or relative like "2023-01-01")}
                            {end_date : End date (YYYY-MM-DD or relative like "yesterday")}
                            {platform?}';
    protected $description = 'Fix faulty PUIDs for a platform directly in the db';

    /**
     * Execute the console command.
     */
    public function handle(OpenSearch $opensearch)
    {
        $startDate = $this->parseDateArgument($this->argument('start_date'));
        $endDate = $this->parseDateArgument($this->argument('end_date'));

        $platform = $this->argument('platform') ?? 'Discord Netherlands B.V.';
        $platform = Platform::where('name', $platform)->first();

        if (!$platform) {
            $this->error("Could not find platform");
            return;
        }

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $start = Carbon::parse($date)->startOfDay();
            $end   = Carbon::parse($date)->endOfDay();

            $table = $date->lt('2025-07-01 00:00:00') ? 'statements' : 'statements_beta';
            $query = DB::connection('mysql::read')
                ->table($table)
                ->whereBetween('created_at', [$start, $end]);
            $minId = $query->min('id');
            $maxId = $query->max('id');

            StatementFixPuidDbIdChunk::dispatch($platform->id, $minId, $maxId, $table);
            $this->info("Dispatched job for {$date->toDateString()}: {$table} with IDs from {$minId} to {$maxId}");
        }
    }

    private function parseDateArgument(string $date): Carbon
    {
        // Handle relative dates like "yesterday", "today", etc.
        if (in_array($date, ['yesterday', 'today', 'tomorrow'])) {
            return Carbon::parse($date);
        }

        // Handle relative formats like "1 week ago", "2 days ago"
        if (preg_match('/^\d+\s+(day|week|month|year)s?\s+ago$/', $date)) {
            return Carbon::parse($date);
        }

        // Handle absolute dates
        try {
            return Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            // Try parsing as a general date string
            return Carbon::parse($date);
        }
    }
}
