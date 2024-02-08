<?php

namespace App\Console\Commands;

use App\Services\StatementSearchService;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class StatementsAggregatesCsv extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:aggregatescsv {token} {endpoint=https://transparency.dsa.ec.europa.eu/api/v1/opensearch/aggregates-csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a CSV for all dates';

    /**
     * Execute the console command.
     * @throws GuzzleException
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        $start = Carbon::create(2023, 9, 25);
        $end = Carbon::yesterday()->clone();

        $endpoint = $this->argument('endpoint');
        $token = $this->argument('token');

        if (!$token) {
            $this->error('Token is required!');
            return;
        }

        $file = 'storage/app/sor-aggregates.csv';
        $out = fopen($file, 'wb');
        $first = 1;

        while($start <= $end) {
            $this->info('Getting: ' . $start->format('Y-m-d'));
            $url  = $endpoint . "/" . $start->format('Y-m-d') . "?cache=0&headers=" . $first;
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $result = $client->get($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);
            fwrite($out, $result->getBody()->getContents());
            $first = 0;
            $start->addDay();
        }

        fclose($out);
    }
}
