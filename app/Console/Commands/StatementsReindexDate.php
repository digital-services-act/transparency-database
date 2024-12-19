<?php

namespace App\Console\Commands;

use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use OpenSearch\Client;

/**
 * @codeCoverageIgnore
 */
class StatementsReindexDate extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:reindex-date {index} {target} {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ReIndex statements for a day';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        /** @var Client $client */
        $client = app(Client::class);
        $index  = $this->argument('index');
        $target = $this->argument('target');
        $date = $this->sanitizeDateArgument();

        $first = $day_archive_service->getFirstIdOfDate($date);
        $last = $day_archive_service->getLastIdOfDate($date);

        if ( ! $client->indices()->exists(['index' => $index])) {
            $this->warn('Source index does not exist!');

            return;
        }

        if ( ! $client->indices()->exists(['index' => $target])) {
            $this->warn('Target index does not exist!');

            return;
        }

        if ($first && $last) {



        $result = $client->reindex([
            'wait_for_completion' => false,
            'body'                => [
                'conflicts' => "proceed",
                'source'    => [
                    'index' => $index,
                    "query" => [
                        "bool" => [
                            "filter"               => [
                                [
                                    "range" => [
                                        "id" => [
                                            "from"          => $first,
                                            "to"            => $last,
                                            "include_lower" => true,
                                            "include_upper" => true,
                                            "boost"         => 1.0
                                        ]
                                    ]
                                ]
                            ],
                            "adjust_pure_negative" => true,
                            "boost"                => 1.0
                        ]
                    ]
                ],
                'dest'      => [
                    'index'   => $target,
                    'op_type' => 'create'
                ]
            ]
        ]);

        } else {
            $this->info('Could not find the first or last ids: ' . $first . ' :: ' . $last);
        }
    }
}
