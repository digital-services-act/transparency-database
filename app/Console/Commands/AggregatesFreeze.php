<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonException;

/**
 * @codeCoverageIgnore
 */
class AggregatesFreeze extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aggregates-freeze {date=160}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Freeze and save the aggregates for a date to the s3';

    /**
     * Execute the console command.
     * @throws JsonException
     */
    public function handle(StatementElasticSearchService $statement_elastic_search_service): void
    {
        $date = $this->sanitizeDateArgument();

        $attributes = $statement_elastic_search_service->getAllowedAggregateAttributes();

        $disk = Storage::disk('s3ds');
        $path = Storage::path('');
        $json_file = 'aggregates-' . $date->format('Y-m-d') . '.json';
        $csv_file = 'aggregates-' . $date->format('Y-m-d') . '.csv';

        $results = $statement_elastic_search_service->processDateAggregate(
            $date,
            $attributes,
            false
        );

        Log::info('Number of aggregates in the aggregates freeze results: ' . count($results['aggregates']));

        if (count($results['aggregates']) === 0)
        {
            Log::info('The number of aggregates in the aggregates freeze results is 0, waiting 10 seconds and trying again');
            sleep(10);
            $results = $statement_elastic_search_service->processDateAggregate(
                $date,
                $attributes,
                false
            );
            Log::info('Number of aggregates in the aggregates freeze results: ' . count($results['aggregates']));
        }

        if (count($results['aggregates']) === 0)
        {
            Log::info('The number of aggregates in the aggregates freeze results is 0, waiting 20 seconds and trying again');
            sleep(20);
            $results = $statement_elastic_search_service->processDateAggregate(
                $date,
                $attributes,
                false
            );
            Log::info('Number of aggregates in the aggregates freeze results: ' . count($results['aggregates']));
        }


        if (count($results['aggregates']) === 0)
        {
            Log::warning('The number of aggregates in the aggregates freeze results is still 0, exiting');
            exit;
        }

        // Make the CSV
        $headers = $statement_elastic_search_service->getAllowedAggregateAttributes();
        $headers[] = 'platform_name';
        $headers[] = 'total';
        $headers = array_diff($headers, ['platform_id']);

        $out = fopen($path . $csv_file, 'wb');
        fputcsv($out, $headers);
        foreach ($results['aggregates'] as $result)
        {
            $row = [];
            foreach ($headers as $header)
            {
                $row[] = $result[$header];
            }

            fputcsv($out, $row);
        }

        fclose($out);
        $disk->put($csv_file, fopen($path . $csv_file, 'rb+'));
        unlink($path . $csv_file);

        // Now do the JSON
        $disk->put($json_file, json_encode($results, JSON_THROW_ON_ERROR));
    }
}
