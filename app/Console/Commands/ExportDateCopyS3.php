<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExportCopyS3;
use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExportDateCopyS3 extends Command
{
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
        $date = $this->argument('date');

        if ($date === 'yesterday') {
            $date = Carbon::yesterday();
        } else {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $date);
            } catch (Exception $e) {
                $this->error('Issue with the date provided, checked the format yyyy-mm-dd');
                return;
            }
        }

        $date_string = $date->format('Y-m-d');
        $exports = $day_archive_service->buildBasicArray();
        $versions = ['full', 'light'];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $zip = 'sor-' . $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip';
                $sha1 = 'sor-' .  $export['slug'] . '-' . $date_string . '-' . $version . '.csv.zip.sha1';
                StatementCsvExportCopyS3::dispatch($zip, $sha1)->onQueue('s3copy');
            }
        }
    }
}
