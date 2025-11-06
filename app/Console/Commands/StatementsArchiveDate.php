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
    protected $signature = 'statements:archive-date {date?} {chunk=3000}';

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
        $chunk = $this->intifyArgument('chunk');
        $date = $this->argument('date');

        if (!$date) {
            // Get the date exactly six months ago which could be more or less than 181 days
            $date = Carbon::now()->subMonths(6)->subDay(1);
        } else {
            $date = $this->sanitizeDateArgument();
        }

        $query = [];
        if (env('APP_ENV_REAL') === 'production') {
            $query = [
                'match' => [
                    'received_date' => $date->getTimestampMs(),
                ],
            ];
        } else {
            $query = [
                'bool' => [
                    'must' => [
                        'range' => [
                            'created_at' => [
                                'gte' => $date->startOfDay()->getTimestampMs(),
                                'lte' => $date->endOfDay()->getTimestampMs(),
                            ],
                        ],
                    ],
                ],
            ];
        }

        $osCount = 0;

        try {
            $osCount = $client->count([
                'index' => 'statement_index',
                'body' => [
                    'query' => $query,
                ],
            ]);
            $osCount = $osCount['count'];
        } catch (\Exception $e) {
            Log::error('Error getting count from OpenSearch for date ' . $date->format('Y-m-d') . ': ' . $e->getMessage());
        }

        if ($osCount) {
            Log::info('Statement Archiving Started', ['date' => $date->format('Y-m-d'), 'at' => Carbon::now()->format('Y-m-d H:i:s')]);

            // Normally at this point we would start the process of removing
            // statements from the DB and "archiving" them.
            // However, this part of the process has not been fully greenlit.
            //
            // This is what would normally would have been
            // StatementArchiveRange::dispatch($min, $max, $chunk);
            //
            // In practice there is no evidence that simply keeping the statements
            // in the DB is going to harm things and by having them there
            // ultimately we can rely on them existing.
            //
            // The data retention policy will evolve over time and this maybe revisited.

            // Clear Opensearch records for that day
            Artisan::call('opensearch:delete-date-range', [
                'start' => $date->format('Y-m-d'),
            ]);

            return;
        }

        Log::warning('Not able to obtain the highest or lowest ID for the day: ' . $date->format('Y-m-d'));
    }
}
