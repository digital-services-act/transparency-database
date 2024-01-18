<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExportZipParts;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;

class ExportDateZipCsvParts extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exportcsv:zipcsvparts {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'zip the csv files.';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $date = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');
        $exports     = $day_archive_service->buildBasicArray();
        $versions    = ['full', 'light'];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                StatementCsvExportZipParts::dispatch($date_string, $export['slug'], $version);
            }
        }
    }
}
