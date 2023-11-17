<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetAggregates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:aggregates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Your start and end dates
//        $startDate = Carbon::parse('2023-09-23');
//        $endDate = Carbon::parse('2023-10-24');
        [$startDate, $endDate] = $this->getMinMaxDates();

        Log::info($startDate);
        Log::info($endDate);



// Loop between two dates (inclusive)
        while ($startDate->lte($endDate)) {
            // Your code here
            echo $startDate->toDateString() . PHP_EOL;

            $this->call('generate:aggregates', [
                'date' => $startDate->toDateString(),
                '--force' => true,
            ]);

            // Increment the date by one day
            $startDate->addDay();
        }
    }

    private function getMinMaxDates()
    {
        $result = DB::table('statements')
            ->select(DB::raw('MIN(created_at) as min_date'), DB::raw('MAX(created_at) as max_date'))
            ->first();

        $minDate = Carbon::parse($result->min_date);
        $maxDate = Carbon::parse($result->max_date);


        return [
            $minDate,
            $maxDate
        ];
    }
}
