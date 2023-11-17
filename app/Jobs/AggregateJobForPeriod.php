<?php

namespace App\Jobs;

use App\Models\SqlAggregate;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateJobForPeriod implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $start;
    public int $end;
    public Carbon $date;
    public string $formattedDate;
    public string $formattedDateAfter;

    /**
     * Create a new job instance.
     *
     * @param int $start
     * @param int $end
     * @return void
     */
    public function __construct(int $start, int $end, Carbon $date)
    {
        $this->start = $start;
        $this->end = $end;
        $this->date = $date;
        $this->formattedDate = $date->format('Y-m-d');
        $this->formattedDateAfter = (clone $date)->addDay()->format('Y-m-d');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $diff = $this->end - $this->start;
        Log::info("Loading from {$this->start} to {$this->end} ({$diff})");

        $query = "select count(*) as sor_count, platform_id, statements.category, statements.decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type from statements
where id >= {$this->start} and id < {$this->end} and (created_at >= '{$this->formattedDate}' and created_at < '{$this->formattedDateAfter}')
group by statements.platform_id, statements.category, decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type";


        $results = DB::select($query);

        // Step 2: Convert the results to instances of YourModel
        $models = collect($results)->map(function ($result) {
            return SqlAggregate::create((array)$result);
        });

// Step 3: Save each model instance to the database
        foreach ($models as $model) {
            $model->start_date = $this->date->format("Y-m-d 00:00:00");;
            $model->end_date = $this->date->format("Y-m-d 00:00:00");;
            $model->save();
        }
    }
}
