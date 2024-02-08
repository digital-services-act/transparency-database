<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExport;
use App\Jobs\StatementCsvExportArchive;
use App\Jobs\StatementCsvExportCopyS3;
use App\Jobs\StatementCsvExportReduce;
use App\Jobs\StatementCsvExportSha1;
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
            $this->error('In order to make day archives, you need to define the "s3ds" bucket.');

            return;
        }

        $date = $this->sanitizeDateArgument();

        $date_string = $date->format('Y-m-d');
        $exports     = $day_archive_service->buildBasicExportsArray();
        $versions    = ['full', 'light'];
        $chunk       = 500000;
        $first_id    = $day_archive_service->getFirstIdOfDate($date);
        $last_id     = $day_archive_service->getLastIdOfDate($date);
        $current     = $first_id;
        $part        = 0;

        $csv_export_jobs = [];
        while ($current <= $last_id) {
            $till              = ($current + $chunk - 1);
            $csv_export_jobs[] = new StatementCsvExport($date_string, sprintf('%05d', $part), $current, $till, $part === 0);
            ++$part;
            $current += $chunk;
        }

        $reduce_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $reduce_jobs[] = new StatementCsvExportReduce($date_string, $export['slug'], $version);
            }
        }

        $zip_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $zip_jobs[] = new StatementCsvExportZipParts($date_string, $export['slug'], $version);
            }
        }

        $sha1_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $sha1_jobs[] = new StatementCsvExportSha1($date_string, $export['slug'], $version);
            }
        }

        $copys3_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $zip           = 'sor-' . $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip';
                $sha1          = 'sor-' . $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip.sha1';
                $copys3_jobs[] = new StatementCsvExportCopyS3($zip, $sha1);
            }
        }

        $archive_jobs = [];
        foreach ($exports as $export) {
            $archive_jobs[] = new StatementCsvExportArchive($date_string, $export['slug'], $export['id']);
        }

        $luggage = ['date_string' => $date_string, 'archive_jobs' => $archive_jobs, 'csv_export_jobs' => $csv_export_jobs, 'sha1_jobs' => $sha1_jobs, 'copys3_jobs' => $copys3_jobs, 'zip_jobs' => $zip_jobs, 'reduce_jobs' => $reduce_jobs];

        Log::info('Day Archiving Started for: ' . $date_string . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
        File::delete(File::glob(storage_path('app') . '/*' . $date_string . '*'));
        Bus::batch($luggage['csv_export_jobs'])->finally(static function () use ($luggage) {
            Bus::batch($luggage['reduce_jobs'])->finally(static function () use ($luggage) {
                Bus::batch($luggage['zip_jobs'])->finally(static function () use ($luggage) {
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
