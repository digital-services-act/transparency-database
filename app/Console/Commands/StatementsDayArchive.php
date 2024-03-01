<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExport;
use App\Jobs\StatementCsvExportArchive;
use App\Jobs\StatementCsvExportCopyS3;
use App\Jobs\StatementCsvExportGroupParts;
use App\Jobs\StatementCsvExportReduce;
use App\Jobs\StatementCsvExportSha1;
use App\Jobs\StatementCsvExportZipPart;
use App\Jobs\StatementCsvExportZipParts;
use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class StatementsDayArchive extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:day-archive {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a day archive compile jobs.';

    /**
     * Execute the console command.
     * @throws Exception
     * @throws Throwable
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        if ( ! config('filesystems.disks.s3ds.bucket')) {
            Log::error('In order to make day archives, you need to define the "s3ds" bucket.');

            return;
        }

        $test = glob('storage/app/sor*');
        if(count($test)) {
            Log::error('Archiving can not run, day archive already in progress');
            return;
        }

        $date        = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');

        $exports  = $day_archive_service->buildBasicExportsArray();
        $versions = ['full', 'light'];
        $chunk    = 10000000;
        $first_id = $day_archive_service->getFirstIdOfDate($date);
        $last_id  = $day_archive_service->getLastIdOfDate($date);

        $current  = $first_id;
        $part     = 0;

        // Get the SOR from the DB into csv chunks
        $csv_export_jobs = [];
        while ($current <= $last_id) {
            $till = ($current + $chunk - 1);
            $till = min($till, $last_id);
            //$csv_export_jobs[] = new StatementCsvExport($date_string, sprintf('%05d', $part), $current, $till, $part === 0);
            // Always headers
            $csv_export_jobs[] = new StatementCsvExport($date_string, sprintf('%05d', $part), $current, $till, true);
            ++$part;
            $current += $chunk;
        }

        // Get rid of any blank parts
        $reduce_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $reduce_jobs[] = new StatementCsvExportReduce($date_string, $export['slug'], $version);
            }
        }

        // Method B:
        // This will the zip the individual csvs to separate zips.
        $zip_part_jobs = [];
        $total_parts   = count($csv_export_jobs);
        $part          = 0;
        while ($part <= $total_parts) {
            foreach ($exports as $export) {
                foreach ($versions as $version) {
                    $zip_part_jobs[] = new StatementCsvExportZipPart($date_string, $export['slug'], $version, sprintf('%05d', $part));
                }
            }
            ++$part;
        }

        // This will store with no compression the zips into one zip.
        $group_zip_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $group_zip_jobs[] = new StatementCsvExportGroupParts($date_string, $export['slug'], $version);
            }
        }

        // Method A:
        // This will zip the csv into one zip.
        $zip_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $zip_jobs[] = new StatementCsvExportZipParts($date_string, $export['slug'], $version);
            }
        }

        // Generate sha1s for the main zip.
        $sha1_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $sha1_jobs[] = new StatementCsvExportSha1($date_string, $export['slug'], $version);
            }
        }

        // Copy what we need to s3
        $copys3_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $zip           = 'sor-' . $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip';
                $sha1          = 'sor-' . $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip.sha1';
                $copys3_jobs[] = new StatementCsvExportCopyS3($zip, $sha1);
            }
        }

        // Create DB Entries to show on the data download page.
        $archive_jobs = [];
        foreach ($exports as $export) {
            $archive_jobs[] = new StatementCsvExportArchive($date_string, $export['slug'], $export['id']);
        }

        // Hold and carry all the possible jobs.
        $luggage = [
            'date_string'     => $date_string,
            'archive_jobs'    => $archive_jobs,
            'zip_part_jobs'   => $zip_part_jobs,
            'group_zip_jobs'  => $group_zip_jobs,
            'csv_export_jobs' => $csv_export_jobs,
            'sha1_jobs'       => $sha1_jobs,
            'copys3_jobs'     => $copys3_jobs,
            'zip_jobs'        => $zip_jobs,
            'reduce_jobs'     => $reduce_jobs
        ];

        Log::info('Day Archiving Started for: ' . $date_string . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
        File::delete(File::glob(storage_path('app') . '/*' . $date_string . '*'));
        Bus::batch($luggage['csv_export_jobs'])->onQueue('csv')->finally(static function () use ($luggage) {
            Bus::batch($luggage['zip_part_jobs'])->onQueue('zip')->finally(static function () use ($luggage) {
                Bus::batch($luggage['group_zip_jobs'])->onQueue('zip')->finally(static function () use ($luggage) {
                    Bus::batch($luggage['sha1_jobs'])->finally(static function () use ($luggage) {
                        Bus::batch($luggage['copys3_jobs'])->onQueue('s3copy')->finally(static function () use ($luggage) {
                            Bus::batch($luggage['archive_jobs'])->finally(static function () use ($luggage) {
                                File::delete(File::glob(storage_path('app') . '/*' . $luggage['date_string'] . '*'));
                                Log::info('Day Archiving Ended for: ' . $luggage['date_string'] . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
                            })->dispatch();
                        })->dispatch();
                    })->dispatch();
                })->dispatch();
            })->dispatch();
        })->dispatch();
    }
}
