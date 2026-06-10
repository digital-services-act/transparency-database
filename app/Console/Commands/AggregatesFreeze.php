<?php

namespace App\Console\Commands;

use App\Services\DayArchiveWorkspace;
use App\Services\StatementElasticAggregationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonException;

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
     *
     * @throws JsonException
     */
    public function handle(StatementElasticAggregationService $statement_elastic_aggregation_service, DayArchiveWorkspace $day_archive_workspace): void
    {
        $date = $this->sanitizeDateArgument();

        $attributes = $statement_elastic_aggregation_service->getAllowedAggregateAttributes();

        $json_file = 'aggregates-'.$date->format('Y-m-d').'.json';
        $csv_file = 'aggregates-'.$date->format('Y-m-d').'.csv';

        $results = $statement_elastic_aggregation_service->processDateAggregate(
            $date,
            $attributes,
            false
        );

        Log::info('Number of aggregates in the aggregates freeze results: '.count($results['aggregates']));

        if (count($results['aggregates']) === 0) {
            Log::error('The number of aggregates in the aggregates freeze results is 0');

            return;
        }

        // Make the CSV
        $csv_path = $day_archive_workspace->path($csv_file);
        $headers = $statement_elastic_aggregation_service->getAllowedAggregateAttributes();
        $headers[] = 'platform_name';
        $headers[] = 'total';
        $headers = array_diff($headers, ['platform_id']);

        $out = fopen($csv_path, 'wb');
        try {
            fputcsv($out, $headers);
            foreach ($results['aggregates'] as $result) {
                $row = [];
                foreach ($headers as $header) {
                    $row[] = $result[$header];
                }

                fputcsv($out, $row);
            }
        } finally {
            if (is_resource($out)) {
                fclose($out);
            }
        }

        $disk = Storage::disk('s3ds');
        $csv = fopen($csv_path, 'rb');
        try {
            $disk->put($csv_file, $csv);
        } finally {
            if (is_resource($csv)) {
                fclose($csv);
            }

            File::delete($csv_path);
        }

        // Now do the JSON
        $disk->put($json_file, json_encode($results, JSON_THROW_ON_ERROR));
    }
}
