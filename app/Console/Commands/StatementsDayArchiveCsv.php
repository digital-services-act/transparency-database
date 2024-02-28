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

class StatementsDayArchiveCsv extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:day-archive-csv {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export to CSV the files needed.';

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



        $date        = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');

        $exports  = $day_archive_service->buildBasicExportsArray();
        $versions = ['full', 'light'];
        $chunk    = 1000000;
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

        $luggage = [];
        $luggage['date_string'] = $date_string;
        $luggage['csv_export_jobs'] = $csv_export_jobs;

        Log::info('Day Export to CSV Started for: ' . $date_string . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
        File::delete(File::glob(storage_path('app') . '/*' . $date_string . '*.csv'));
        Bus::batch($luggage['csv_export_jobs'])->finally(static function () use ($luggage) {
            Log::info('Day Export to CSV End for: ' . $luggage['date_string'] . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
        })->dispatch();
    }
}
