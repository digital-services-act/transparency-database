<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExportZip;
use App\Jobs\StatementCsvExportZipPart;
use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ExportDateZipCsvParts extends Command
{
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
        $path = Storage::path('');
        $exports = $day_archive_service->buildBasicArray();
        $versions = ['full', 'light'];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $parts = $path . 'sor-' . $version . '-' . $export['slug'] . '-' . $date_string . '-*.csv';
                $partsglob = glob($parts);
                foreach ($partsglob as $part) {
                    StatementCsvExportZipPart::dispatch($part);
                }
            }
        }
    }
}
