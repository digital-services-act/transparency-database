<?php

namespace App\Jobs;

use App\Services\DayArchiveService;
use App\Services\PlatformQueryService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * @codeCoverageIgnore
 *
 * This job handles exporting statements to CSV files and compressing them into ZIP archives.
 * It processes statements in chunks, organizes them by platform, and creates both full and light versions
 * of the CSV files. The resulting ZIP files are stored in a specified storage location.
 *
 * Testing this job would require setting up a large dataset and verifying the output files,
 * which is beyond the scope of typical unit tests.
 *
 * Nearly every line of this class is documented and would be impractical to cover with unit tests.
 *
 * The result should be a zip file with 1 million statements in it, spread over 10 csv
 * files containing 100,000 statements each.
 */
class StatementCsvExportZ implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $statements_table = 'statements_beta';

    public function __construct(public string $date, public string $part, public int $start_id, public int $end_id, public bool $headers = false) {}

    public function zipFilePathForSlugAndVersion(string $slug, string $version): string
    {
        return Storage::path('sor-'.$slug.'-'.$this->date.'-'.$version.'-'.$this->part.'.csv.zip');
    }

    public function csvFilenameForSlugAndVersionAndSubpart(string $slug, string $version, int $subpart): string
    {
        return 'sor-'.$slug.'-'.$this->date.'-'.$version.'-'.$this->part.'-'.sprintf('%05d', $subpart).'.csv';
    }

    public function handle(DayArchiveService $day_archive_service, PlatformQueryService $platform_query_service): void
    {
        // Fetch platforms
        $platforms = $platform_query_service->getPlatformsById();

        // Prepare exports array for global and each platform
        $exports = $day_archive_service->buildBasicExportsArray();

        // Get the raw select string for querying statements
        $select_raw = $day_archive_service->getSelectRawString();

        // Prepare CSV headings
        $headings = $day_archive_service->prepareHeadingsArray();

        // Start processing from the initial ID
        $current_start = $this->start_id;

        // Initialize subparts for each export version
        foreach (array_keys($exports) as $index) {
            foreach ($day_archive_service->versions as $version) {
                $exports[$index]['subparts'][$version] = [];
            }
        }

        $subpart = 0;
        while ($current_start <= $this->end_id) {

            // Initialize subparts for this chunk
            foreach (array_keys($exports) as $index) {
                foreach ($day_archive_service->versions as $version) {
                    $exports[$index]['subparts'][$version][$subpart] = [];
                }
            }

            // determine the end ID for this chunk
            $current_end = min(($current_start + $day_archive_service->chunk), $this->end_id);

            // Fetch statements in the current chunk
            $statements = $day_archive_service->getRawStatements($current_start, $current_end);

            // Process each statement
            foreach ($statements as $statement) {

                // Convert statement to csv strings for each version
                $csvs = $day_archive_service->buildCsvLines($statement);

                // Add to global (0) export foreach version
                foreach ($day_archive_service->versions as $version) {
                    $exports[0]['subparts'][$version][$subpart][] = $csvs[$version];
                }

                // Maybe put it also in a platform-specific export foreach version
                if (isset($exports[$statement->platform_id])) {
                    foreach ($day_archive_service->versions as $version) {
                        $exports[$statement->platform_id]['subparts'][$version][$subpart][] = $csvs[$version];
                    }
                }
            }

            // Move to the next chunk
            $subpart++;
            // Reset the current start for the next chunk
            $current_start += $day_archive_service->chunk + 1;
        }

        // The csv lines have all been created in memory.
        // global->full and global->light and each platform->full and platform->light
        // the exports array has all the data needed to create the zip files.
        // the structure is:
        // [
        //     ['id' => null, 'slug' => 'global', 'subparts' => ['full' => [], 'light' => []]],
        //     ['id' => 1, 'slug' => 'platform-name', 'subparts' => ['full' => [], 'light' => []]],
        //     ...
        // ]
        // Each export has subparts for each version with the csv lines.

        // Now create the zip files for each export and version
        foreach ($exports as $export) {

            // Create a zip file for each version
            foreach ($day_archive_service->versions as $version) {

                $platform_slug = $export['slug']; // global or platform slug

                // Create the zip file name holder for local storage
                $zip_file = $this->zipFilePathForSlugAndVersion($platform_slug, $version);

                // Create the zip and add files from memory
                $zip = new \ZipArchive;
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                // Add each subpart file to the zip
                foreach ($export['subparts'][$version] as $subpart => $rows) {
                    if ($subpart === 0 || count($rows)) {
                        // Create the CSV file name
                        $csv_file = $this->csvFilenameForSlugAndVersionAndSubpart($platform_slug, $version, $subpart);

                        // Add the CSV file to the zip from memory
                        $zip->addFromString($csv_file, $headings[$version]."\n".implode("\n", $rows));
                    }
                }

                // Finalize the zip file
                $zip->close();
            }
        }
    }
}
