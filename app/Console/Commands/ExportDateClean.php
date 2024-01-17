<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExportClean;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExportDateClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exportcsv:clean {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up and remove all files for a date.';

    /**
     * Execute the console command.
     */
    public function handle(): void
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

        StatementCsvExportClean::dispatch($date_string);
    }
}
