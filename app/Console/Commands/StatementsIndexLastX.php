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
    protected $signature = 'statements:index-last-x {minutes=6}';

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
        $start = Carbon::now();
        $start->subMinutes((int)$this->argument('minutes'));

        $statement_ids = Statement::query()->select(['id'])->where('created_at', '>=', $start)->pluck('id');

        Log::info('Indexing: ' .  $statement_ids->count());

        $statement_ids->chunk(400)->each(function($bag){
            StatementIndexBag::dispatch($bag->toArray());
        });
    }
}
