<?php

namespace App\Console\Commands;

use App\Models\SqlAggregate;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateAggregates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:aggregates {date=yesterday}';

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

        $date = $this->argument('date');
        if ($date === 'yesterday') {
            $date = \Illuminate\Support\Carbon::yesterday();
        } else {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $date);
            } catch (Exception $e) {
                $this->error('Issue with the date provided, checked the format yyyy-mm-dd');
            }
        }


//TODO: Check if we already have aggregates for that day and refuse to go or delete if --force

        $formatted_date = $date->format('Y-m-d');
        //Get the min and max id
        $query_min = "select min(id) as min from statements where DATE(created_at) = '{$formatted_date}'";
        $result_min = DB::select($query_min);

        $min_id = isset($result_min[0]) ? $result_min[0]->min : null;

        if(is_null($min_id)){
            $this->error("No records available for the date ({$formatted_date})");
            return false;
        }

        $query_max = "select max(id) as max from statements where DATE(created_at) = '{$formatted_date}'";
        $result_max = DB::select($query_max);

        $max_id = isset($result_max[0]) ? $result_max[0]->max : null;

        $this->info("MIN id: " . $min_id);
        $this->info("MAX id: " . $max_id);

        dd('processing continues');


        for ($hour = 0; $hour <= 23; $hour++) {

            $start_hour = sprintf('%02d', $hour);

            $start_hour_date_limit1 = $date->format("Y-m-d {$start_hour}:00:00");
            $start_hour_date_limit2 = $date->format("Y-m-d {$start_hour}:01:00");

            $end_hour_date_limit1 = $date->format("Y-m-d {$start_hour}:59:59");
            $end_hour_date_limit2 = $date->format("Y-m-d {$start_hour}:59:00");

            $this->info("START - " . $start_hour_date_limit1);

            $min = DB::table('statements')->selectRaw('MIN(id) AS min')->where([
                ['created_at', ">=", $start_hour_date_limit1],
                ['created_at', "<", $start_hour_date_limit2],
            ])->first()->min;

            $max = DB::table('statements')->selectRaw('MAX(id) AS max')->where([
                ['created_at', "<=", $end_hour_date_limit1],
                ['created_at', ">", $end_hour_date_limit2],
            ])->first()->max;

            $this->info("MIN id: " . $min);
            $this->info("MAX id: " . $max);

            // Run the query
            $query = "select count(*) as sor_count, platform_id, statements.category, statements.decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type from statements
where id >= {$min} and id <= {$max}
group by statements.platform_id, statements.category, decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type";


            $results = DB::select($query);

            // Step 2: Convert the results to instances of YourModel
            $models = collect($results)->map(function ($result) {
                return SqlAggregate::create((array)$result);
            });

// Step 3: Save each model instance to the database
            foreach ($models as $model) {
                $model->start_date = $start_hour_date_limit1;
                $model->end_date = $end_hour_date_limit1;
                $model->save();
            }

            $this->info("END - " . $end_hour_date_limit1);

        }
        $this->computeDailyAggregates($date);

    }

    /**
     * @param \Illuminate\Support\Carbon|bool|array|Carbon|string|null $date
     * @return void
     */
    public function computeDailyAggregates(\Illuminate\Support\Carbon|bool|array|Carbon|string|null $date): void
    {
// Hourly are in, let's compute the daily
        $query = "select sum(sor_count) as sor_count, platform_id, category, decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type from sql_aggregates
where DATE(start_date) = '{$date->format('Y-m-d')}'

group by platform_id, category, decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type";

        $results = DB::select($query);

        $models = collect($results)->map(function ($result) {
            return SqlAggregate::create((array)$result);
        });

// Step 3: Save each model instance to the database
        foreach ($models as $model) {
            $model->start_date = $date->format("Y-m-d 00:00:00");
            $model->end_date = $date->format("Y-m-d 23:59:59");
            $model->aggregate_type = "daily";
            $model->save();
        }
    }


}
