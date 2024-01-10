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
    protected $signature = 'statements:index-last-x {seconds=65}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index statements for the last X seconds.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $start = Carbon::now();
        $start->subSeconds((int)$this->argument('seconds'));
        $statement_ids = Statement::query()->select(['id'])->where('created_at', '>=', $start)->pluck('id');

        //Log::info('Indexing: ' .  $statement_ids->count());

        $statement_ids->chunk(600)->each(function($bag){
            StatementIndexBag::dispatch($bag->toArray());
        });
    }
}
