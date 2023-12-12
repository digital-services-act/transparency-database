<?php

namespace App\Console\Commands;

use App\Jobs\StatementIndexRange;
use App\Jobs\VerifyIndex;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use OpenSearch\Client;


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
    protected $description = 'Index statements for a day';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service, Client $client): void
    {
        $query_chunk = $this->argument('query_chunk') === 'default' ? 10000 : (int)$this->argument('query_chunk');
        $searchable_chunk = $this->argument('searchable_chunk') === 'default' ? 100 : (int)$this->argument('searchable_chunk');
        $date = $this->argument('date') === 'default' ? Carbon::yesterday() : Carbon::createFromFormat('Y-m-d', $this->argument('date'));

        $min = $day_archive_service->getFirstIdOfDate($date);
        $max = $day_archive_service->getLastIdOfDate($date);

        if ($min && $max) {
            VerifyIndex::dispatch($max, $min, $query_chunk, $searchable_chunk);
        } else {
            Log::warning('Not able to obtain the highest or lowest ID for the day: ' . $date->format('Y-m-d'));
        }
    }
}
