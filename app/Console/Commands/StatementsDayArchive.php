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
use Illuminate\Bus\Batch;
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
     * @throws \Throwable
     */
    public function handle(DayArchiveService $day_archive_service)
    {

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

        $csv_export_jobs = [];
        while($current <= $last_id) {
            $till = ($current + $chunk - 1);
            $csv_export_jobs[] = new StatementCsvExport($date_string, sprintf('%05d', $part), $current, $till, $part === 0);
            $part++;
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
                $sha1_jobs[] = StatementCsvExportSha1::dispatch($date_string, $export['slug'], $version);
            }
        }

        $copys3_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $zip = 'sor-' . $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip';
                $sha1 = 'sor-' .  $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip.sha1';
                $copys3_jobs[] = (new StatementCsvExportCopyS3($zip, $sha1))->onQueue('s3copy');
            }
        }

        $archive_jobs = [];
        foreach ($exports as $export) {
            $archive_jobs = new StatementCsvExportArchive($date_string, $export['slug'], $export['id']);
        }

        $start_jobs = [
            static function() use($date_string) {
                Log::debug('Day Archiving Started for: ' . $date_string . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
            },
            new StatementCsvExportClean($date_string),
        ];

        $finish_jobs = [
            new StatementCsvExportClean($date_string),
            static function() use($date_string) {
                Log::debug('Day Archiving Ended for: ' . $date_string . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
            }
        ];

        $luggage = compact('start_jobs', 'finish_jobs');

        $finish_batch = Bus::batch($luggage['finish_jobs'])->finally(function() use($luggage) {
            $start_batch = Bus::batch($luggage['start_jobs'])->dispatch();
        })->dispatch();


//        $start_batch = $batch = Bus::batch([
//            static function() use($luggage) {
//                Log::debug('Day Archiving Started for: ' . $luggage['date_string'] . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
//            },
//            new StatementCsvExportClean($luggage['date_string']),
//        ])->finally(function (Batch $start_batch) use($luggage) {
//            $csv_export_batch = Bus::batch($luggage['csv_export_jobs'])->finally(function (Batch $csv_export_batch) use($luggage) {
//                $reduce_batch = Bus::batch($luggage['reduce_jobs'])->finally(function (Batch $reduce_batch) use($luggage) {
//                    $zip_batch = Bus::batch($luggage['zip_jobs'])->finally(function(Batch $zip_batch) use($luggage) {
//                        $sha1_batch = Bus::batch($luggage['sha1_jobs'])->finally(function(Batch $sha1_batch) use($luggage) {
//                            $copys3_batch = Bus::batch($luggage['copys3_jobs'])->finally(function(Batch $copys3_batch) use($luggage) {
//                                $archive_batch = Bus::batch($luggage['archive_jobs'])->finally(function(Batch $archive_batch) use($luggage) {
//                                    $finish_batch = Bus::batch([
//                                        new StatementCsvExportClean($luggage['date_string']),
//                                        static function() use($luggage) {
//                                            Log::debug('Day Archiving Ended for: ' . $luggage['date_string'] . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
//                                        }
//                                    ])->dispatch();
//                                })->dispatch();
//                            })->dispatch();
//                        })->dispatch();
//                    })->dispatch();
//                })->dispatch();
//            })->dispatch();
//        })->dispatch();
    }
}
