<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExport;
use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class ExportDateToCsv extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exportcsv:csv {date=yesterday} {chunk=500000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a date of statements to CSV parts and files.';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $date  = $this->sanitizeDateArgument();
        $chunk = (int)$this->argument('chunk');

        $first_id = $day_archive_service->getFirstIdOfDate($date);
        $last_id  = $day_archive_service->getLastIdOfDate($date);
        $current  = $first_id;

        $part        = 0;
        $date_string = $date->format('Y-m-d');

        File::delete(File::glob('storage/app/*' . $date_string . '*.csv'));

        while ($current <= $last_id) {
            $till = ($current + $chunk - 1);
            StatementCsvExport::dispatch($date_string, sprintf('%05d', $part), $current, $till, $part === 0);
            $part++;
            $current += $chunk;
        }
    }
}
