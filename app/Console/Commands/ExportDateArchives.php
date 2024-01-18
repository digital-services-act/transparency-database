<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExportArchive;
use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExportDateArchives extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exportcsv:archives {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create archives entries for platforms and exports.';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $date = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');
        $exports = $day_archive_service->buildBasicArray();
        foreach ($exports as $export) {
            StatementCsvExportArchive::dispatch($date_string, $export['slug'], $export['id']);
        }
    }
}
