<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExportCopyS3;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;

class ExportDateCopyS3 extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exportcsv:copys3 {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Concatenate the individual csv files to one.';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $date = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');
        $exports     = $day_archive_service->buildBasicExportsArray();
        $versions    = ['full', 'light'];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $zip  = 'sor-' . $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip';
                $sha1 = 'sor-' . $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip.sha1';
                StatementCsvExportCopyS3::dispatch($zip, $sha1)->onQueue('s3copy');
            }
        }
    }
}
