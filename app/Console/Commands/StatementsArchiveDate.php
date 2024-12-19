<?php

namespace App\Console\Commands;

use App\Services\DayArchiveService;
use Carbon\Carbon;
use Illuminate\Console\Command;
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
    protected $signature = 'statements:archive-date {date=181} {chunk=3000}';

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
        $date = $this->sanitizeDateArgument();

        $min = $day_archive_service->getFirstIdOfDate($date);
        $max = $day_archive_service->getLastIdOfDate($date);

        if ($min && $max) {
            Log::info('Statement Archiving Started', ['date' => $date->format('Y-m-d'), 'at' => Carbon::now()->format('Y-m-d H:i:s')]);
            $client->deleteByQuery([
                'index' => 'statement_index',
                'body' => [
                    'query' => [
                        'match' => [
                            'received_date' => $date->getTimestampMs()
                        ]
                    ]
                ]
            ]);

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

            return;
        }

        Log::warning('Not able to obtain the highest or lowest ID for the day: ' . $date->format('Y-m-d'));
    }
}
