<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;

/**
 * @codeCoverageIgnore
 */
class FindDuplicatePuids extends Command
{
    protected $signature = 'os:find-duplicate-puids {date}';
    protected $description = 'Find duplicate PUIDs in OpenSearch for a given day';

    public function handle()
    {
        $date = $this->argument('date');

        // Build client
        $client = app(Client::class);

        $fetchSize = 10000;

        // SQL query
        $query = "
            SELECT id, puid
            FROM statement_index
            WHERE created_at between '$date 00:00:00' and '$date 23:59:59'
            ORDER BY id ASC
        ";

        $seen = [];
        $clientParams = [
            'body' => [
                'query'      => $query,
                'fetch_size' => $fetchSize,
            ]
        ];

        $this->info("Starting duplicate scan for date: $date");

        while (true) {
            $response = $client->sql()->query($clientParams);

            if (!empty($response['datarows'])) {
                foreach ($response['datarows'] as $row) {
                    [$id, $puid] = $row;

                    if (isset($seen[$puid])) {
                        $this->error("Duplicate detected!");
                        $this->line("PUID: $puid");
                        $this->line("Old ID: " . $seen[$puid]);
                        $this->line("New ID: " . $id);
                        return Command::SUCCESS;
                    }

                    $seen[$puid] = $id;
                }
            }

            // Handle cursor
            if (!empty($response['cursor'])) {
                $clientParams = [
                    'body' => [
                        'cursor'     => $response['cursor'],
                        'query'      => $query,        // required or OS repeats first page
                        'fetch_size' => $fetchSize,
                    ]
                ];
            } else {
                break; // no more results
            }
        }

        $this->info("Finished — no duplicates found.");
        return Command::SUCCESS;
    }
}
