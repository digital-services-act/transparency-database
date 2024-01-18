<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExportReduce;
use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExportDateCsvReduce extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exportcsv:reduce {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reduce and remove 0 size files';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $date = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');
        $exports = $day_archive_service->buildBasicArray();
        $versions = ['full', 'light'];

        foreach ($exports as $export) {
            foreach ($versions as $version) {
                StatementCsvExportReduce::dispatch($date_string, $export['slug'], $version);
            }
        }
    }
}
