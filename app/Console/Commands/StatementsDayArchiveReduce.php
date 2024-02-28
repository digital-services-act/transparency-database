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

class StatementsDayArchiveReduce extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:day-archive-reduce {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reduce empty files.';

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


        // Get rid of any blank parts
        $reduce_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $reduce_jobs[] = new StatementCsvExportReduce($date_string, $export['slug'], $version);
            }
        }


        // Hold and carry all the possible jobs.
        $luggage = [
            'date_string'     => $date_string,
            'reduce_jobs'     => $reduce_jobs
        ];

        Log::info('Day Archiving Reduction Started for: ' . $luggage['date_string'] . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
        Bus::batch($luggage['reduce_jobs'])->finally(static function () use ($luggage) {
            Log::info('Day Archiving Reduction End for: ' . $luggage['date_string'] . ' at ' . Carbon::now()->format('Y-m-d H:i:s'));
        })->dispatch();
    }
}
