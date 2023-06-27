<?php

namespace App\Console\Commands;

use App\Exports\StatementsExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class GenerateDatasets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:datasets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the datasets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $statementsExport = new StatementsExport();
        Excel::store($statementsExport, 'statements.xlsx','public');
        Excel::store($statementsExport, 'statements.csv','public', \Maatwebsite\Excel\Excel::CSV);
    }
}
