<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        //Get the days


        // Run the query
        $query = "select count(*), platform_id, statements.category, statements.decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type from statements
where id >= 114345387 and id <= 118037119
group by statements.platform_id, statements.category, decision_ground, decision_account, decision_monetary, decision_provision, automated_detection, automated_decision, content_type, source_type";

    }
}
