<?php

namespace App\Console\Commands;

use App\Jobs\OpenSearchDeleteBatch;
use App\Services\DayArchiveService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use OpenSearch\Client;

/**
 * @codeCoverageIgnore
 */
class StatementsArchiveDate extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:archive-date {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive statements for a day';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service, Client $client): void
    {
        $date = $this->argument('date');

        if (!$date) {
            // Get the date exactly six months ago which could be more or less than 181 days
            $date = Carbon::now()->subMonths(6)->subDay(1);
        } else {
            $date = $this->sanitizeDateArgument();
        }

        // Build a set of queries to run against OpenSearch:
        // 1) records older than the given date (created before the day's start)
        // 2) for the given date, six 4-hour timespans (0-3:59, 4-7:59, ...)

        $queries = [];

        // 1) older than the date (strictly before start of day)
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        $queries[] = [
            'bool' => [
                'must' => [
                    ['range' => ['created_at' => ['lt' => $dayStart->getTimestampMs()]]],
                ],
            ],
        ];

        // 2) six 4-hour windows for the date
        $hoursPerSlice = 4;
        for ($slice = 0; $slice < 24; $slice += $hoursPerSlice) {
            $sliceStart = $dayStart->copy()->addHours($slice);
            $sliceEnd = $sliceStart->copy()->addHours($hoursPerSlice)->subSecond();

            // Ensure sliceEnd does not pass the day's end
            if ($sliceEnd->greaterThan($dayEnd)) {
                $sliceEnd = $dayEnd;
            }

            $queries[] = [
                'bool' => [
                    'must' => [
                        ['range' => ['created_at' => ['gte' => $sliceStart->getTimestampMs(), 'lte' => $sliceEnd->getTimestampMs()]]],
                    ],
                ],
            ];
        }

        $anyFound = false;

        Log::info('Statement Archiving Started', ['date' => $date->format('Y-m-d'), 'at' => Carbon::now()->format('Y-m-d H:i:s')]);

        foreach ($queries as $idx => $q) {
            try {
                $countResp = $client->count([
                    'index' => 'statement_index',
                    'body' => ['query' => $q],
                ]);

                $count = (int) ($countResp['count'] ?? 0);
            } catch (\Throwable $e) {
                Log::error('Error getting count from OpenSearch for date ' . $date->format('Y-m-d') . ' (query #' . $idx . '): ' . $e->getMessage());
                $count = 0;
            }

            if ($count <= 0) {
                $this->line("No documents for query #{$idx} (count=0), skipping delete.");
                continue;
            }

            $this->info("🗑 Preparing to archive {$count} documents for query." . (string) collect($q));

            $anyFound = true;

            try {
                $client->deleteByQuery([
                    'index' => 'statement_index',
                    'conflicts' => 'proceed',
                    'body' => ['query' => $q],
                    'refresh' => true,
                    'wait_for_completion' => false,
                ]);
            } catch (\Throwable $e) {
                Log::error('Error running deleteByQuery for date ' . $date->format('Y-m-d') . ' (query #' . $idx . '): ' . $e->getMessage());
                $this->error('OpenSearch deleteByQuery failed for query #' . $idx . ': ' . $e->getMessage());
            }
        }

        if (! $anyFound) {
            Log::warning('Not able to obtain any documents to archive for the day: ' . $date->format('Y-m-d'));
        }
    }
}
