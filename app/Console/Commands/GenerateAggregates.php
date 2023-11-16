<?php

namespace App\Console\Commands;

use App\Models\SqlAggregate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateAggregates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:aggregates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Daily Aggregates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Get the min and max for the days
        $date = Carbon::createFromFormat('Y-m-d', '2023-10-09');


//        for ($hour = 0; $hour <= 23; $hour++) {
            // Your code here

//            $hour = str_pad($hour, 2, 0);
        $start_hour = '00';
        $end_hour = '23';
            $start_date = $date->format("Y-m-d {$start_hour}:00:00");
            $end_date = $date->format("Y-m-d {$end_hour}:59:59");

            $this->info("START - " . $start_date);

        $min = DB::table('statements')->selectRaw('MIN(id) AS min')->where([
         ['created_at', ">=", $date->format("Y-m-d 00:00:00")],
         ['created_at', "<", $date->format("Y-m-d 00:01:00")],
        ])->first()->min;

        $max = DB::table('statements')->selectRaw('MAX(id) AS max')->where([
         ['created_at', "<=", $date->format("Y-m-d 23:59:59")],
         ['created_at', ">", $date->format("Y-m-d 23:59:00")],
        ])->first()->max;

        $this->info("MIN id: ". $min);
        $this->info("MAX id: ". $max);

//
//        dd($min . " - " . $max);

//$min  = 53233062;
//$max = 57559157;

//$min  = 53233062;
//$max = 53500000;
        // Run the query
        $query = "select count(*) as sor_count, platform_id, statements.category, statements.decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type from statements
where id >= {$min} and id <= {$max}
group by statements.platform_id, statements.category, decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type";


        $results = DB::select($query);

        // Step 2: Convert the results to instances of YourModel
        $models = collect($results)->map(function ($result) {
            return SqlAggregate::create((array) $result);
        });

// Step 3: Save each model instance to the database
        foreach ($models as $model) {
            $model->start_date = $start_date;
            $model->end_date = $end_date;
            $model->save();
        }

            $this->info("END - " . $end_date);

        }

//        foreach($results as $aggregate){
//
//            SqlAggregate::create(array($aggregate));
//        }
//
//        dd($collection);

//    }
}
