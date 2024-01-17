<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExport;
use App\Jobs\StatementCsvExportArchive;
use App\Jobs\StatementCsvExportClean;
use App\Jobs\StatementCsvExportCopyS3;
use App\Jobs\StatementCsvExportReduce;
use App\Jobs\StatementCsvExportSha1;
use App\Jobs\StatementCsvExportZipParts;
use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class StatementsDayArchive extends Command
{
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
     */
    public function handle(DayArchiveService $day_archive_service)
    {
        Log::debug('Day Archiving has been run for: ' . $this->argument('date'));

        if ( ! config('filesystems.disks.s3ds.bucket')) {
            $this->error('In order to make day archives, you need to define the "s3ds" bucket.');

            return;
        }

        $date = $this->argument('date');
        if ($date === 'yesterday') {
            $date = Carbon::yesterday();
        } else {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $date);
            } catch (Exception $e) {
                $this->error('Issue with the date provided, checked the format yyyy-mm-dd');
            }
        }
        $date_string = $date->format('Y-m-d');
        $exports = $day_archive_service->buildBasicArray();
        $versions = ['full', 'light'];
        $chunk = 500000;
        $first_id = $day_archive_service->getFirstIdOfDate($date);
        $last_id = $day_archive_service->getLastIdOfDate($date);
        $current = $first_id;
        $part = 0;

        $jobs = [];
        $jobs[] = static function() use($date_string) {
            Log::debug('Day Archiving Started for: ' . $date_string . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
        };
        $jobs[] = new StatementCsvExportClean($date_string);

        $jobs[] = static function() use($current, $last_id, $chunk, $part, $date_string) {
            while($current <= $last_id) {
                $till = ($current + $chunk - 1);
                StatementCsvExport::dispatch($date_string, sprintf('%05d', $part), $current, $till, $part === 0);
                $part++;
                $current += $chunk;
            }
        };

        $jobs[] = static function() use($exports, $versions, $date) {
            foreach ($exports as $export) {
                foreach ($versions as $version) {
                    StatementCsvExportReduce::dispatch($date->format('Y-m-d'), $export['slug'], $version);
                }
            }
        };

        $jobs[] = static function() use($exports, $versions, $date_string) {
            foreach ($exports as $export) {
                foreach ($versions as $version) {
                    StatementCsvExportZipParts::dispatch($date_string, $export['slug'], $version);
                }
            }
        };

        $jobs[] = static function() use($exports, $versions, $date_string) {
            foreach ($exports as $export) {
                foreach ($versions as $version) {
                    StatementCsvExportSha1::dispatch($date_string, $export['slug'], $version);
                }
            }
        };

        $jobs[] = static function() use($exports, $versions, $date_string) {
            foreach ($exports as $export) {
                foreach ($versions as $version) {
                    $zip = 'sor-' . $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip';
                    $sha1 = 'sor-' .  $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip.sha1';
                    StatementCsvExportCopyS3::dispatch($zip, $sha1)->onQueue('s3copy');
                }
            }
        };

        $jobs[] = static function () use($exports, $date_string) {
            foreach ($exports as $export) {
                StatementCsvExportArchive::dispatch($date_string, $export['slug'], $export['id']);
            }
        };

        $jobs[] = new StatementCsvExportClean($date_string);
        $jobs[] = static function() use($date_string) {
            Log::debug('Day Archiving Ended for: ' . $date_string . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
        };

        Bus::chain($jobs)->dispatch();
    }
}
