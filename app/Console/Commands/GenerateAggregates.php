<?php

namespace App\Console\Commands;

use App\Jobs\AggregateComputeJob;
use App\Jobs\AggregateJobForPeriod;
use App\Models\SqlAggregate;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateAggregates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:aggregates {date=yesterday} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Daily Aggregates';

    const NUMBER_OF_CHUNKS = 5;

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $date = $this->parseDateArgument();

        if ($this->checkExistingAggregates($date)) {
            return;
        }

        [$minId, $maxId] = $this->getMinMaxIds($date);

        if (is_null($minId)) {
            return;
        }

        $this->logMinMaxIds($minId, $maxId);

        $this->dispatchJobs($minId, $maxId, $date);
    }

    private function parseDateArgument()
    {
        $date = $this->argument('date');
        Log::info("let's start the aggregates compilation for {$date}");

        if ($date === 'yesterday') {
            return Carbon::yesterday();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date);
        } catch (Exception $e) {
            $this->error('Issue with the date provided, check the format yyyy-mm-dd');
            return null;
        }
    }

    private function checkExistingAggregates($date)
    {
        $formattedDate = $date->format('Y-m-d');

        if (SqlAggregate::where('start_date', '=', $formattedDate)->exists()) {
            if (!$this->option('force')) {
                $this->error("Aggregates for the date {$formattedDate} already exist. If you wish to recalculate them, please use the --force option");
                return true;
            } else {
                SqlAggregate::where([
                    ['start_date', '=', $formattedDate],
                ])->delete();
            }
        }

        return false;
    }

    private function getMinMaxIds($date)
    {
        $formattedDate = $date->format('Y-m-d');
        $formattedDateAfter = (clone $date)->addDay()->format('Y-m-d');

//        $queryMin = "SELECT MIN(id) as min FROM statements WHERE DATE(created_at) = '{$formattedDate}'";
        $queryMin = "SELECT MIN(id) as min FROM statements WHERE created_at >= '{$formattedDate}' AND created_at < '{$formattedDateAfter}'";
        $resultMin = DB::select($queryMin);

        $minId = isset($resultMin[0]) ? $resultMin[0]->min : null;

        if (is_null($minId)) {
            $this->error("No records found for the date {$formattedDate}");
            return [null, null];
        }

        $queryMax = "SELECT MAX(id) as max FROM statements WHERE created_at >= '{$formattedDate}' AND created_at < '{$formattedDateAfter}'";

//        $queryMax = "SELECT MAX(id) as max FROM statements WHERE DATE(created_at) = '{$formattedDate}'";
        $resultMax = DB::select($queryMax);

        $maxId = isset($resultMax[0]) ? $resultMax[0]->max : null;

        return [$minId, $maxId];
    }

    private function logMinMaxIds($minId, $maxId)
    {
        Log::info("MIN id: " . $minId);
        Log::info("MAX id: " . $maxId);
        Log::info($maxId - $minId);
    }

    private function dispatchJobs($minId, $maxId, $date)
    {
        $step = ceil(($maxId - $minId) / self::NUMBER_OF_CHUNKS);

        $jobs = [];

        for ($start = $minId; $start < $maxId; $start += $step) {
            $end = $start + $step;

            // Ensure the last job considers the final record of the day
            if ($end >= $maxId) {
                $end = $maxId + 1;
            }

            $jobs[] = new AggregateJobForPeriod($start, $end, $date);
        }

        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($date) {
                Log::info("Failures?");
                dispatch(new AggregateComputeJob($date));
            })->catch(function (Batch $batch, Throwable $e) {
                // First batch job failure detected...
                Log::error("Something bad happened while computing the aggregates");
                print_r($e->getTraceAsString());
            })->dispatch();
    }
}
