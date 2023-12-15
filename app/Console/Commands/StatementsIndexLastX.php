<?php

namespace App\Console\Commands;

use App\Jobs\StatementIndexBag;
use App\Models\Statement;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StatementsIndexLastX extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:index-last-x {minutes=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index statements for the last X minutes.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // We can't try to index right to last second. The query can hang.
        // So we shift 2 minutes back.

        $start = Carbon::now();
        $ago = (int)$this->argument('minutes');
        $start->subMinutes($ago)->subMinutes(3);

        $end = Carbon::now();
        $end->subMinutes(2);

        $statement_ids = Statement::query()->select(['id'])->where('created_at', '>=', $start)->where('created_at', '<=', $end)->pluck('id');

        $statement_ids->chunk(400)->each(function($bag){
            StatementIndexBag::dispatch($bag->toArray());
        });
    }
}
