<?php

namespace App\Jobs;

use App\Models\SqlAggregate;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateComputeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $date;
    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $formatted_date = $this->date->format('Y-m-d');
//// Hourly are in, let's compute the daily
        $query = "select sum(sor_count) as sor_count, platform_id, category, decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type from sql_aggregates
where DATE(start_date) = '{$formatted_date}' and aggregate_type = 'HOURLY'
group by platform_id, category, decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type";

        $results = DB::select($query);

        $models = collect($results)->map(function ($result) {
            return SqlAggregate::create((array)$result);
        });

// Step 3: Save each model instance to the database
        foreach ($models as $model) {
            $model->start_date = $this->date->format("Y-m-d 00:00:00");
            $model->end_date = $this->date->format("Y-m-d 23:59:59");
            $model->aggregate_type = "DAILY";
            $model->save();
        }

        //Delete hourly ones

        $report = DB::table('sql_aggregates')
            ->selectRaw('sum(sql_aggregates.sor_count) as sum')
            ->groupBy('start_date')
            ->where([
                ['aggregate_type','=','DAILY'],
                ['start_date','=',$formatted_date],
            ])
            ->first();

        Log::info("Number of SoRs aggregated for {$formatted_date}: " . $report->sum);
    }
}
