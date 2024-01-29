<?php

namespace App\Console\Commands;

use App\Jobs\StatementIndexBag;
use App\Models\Statement;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class StatementsIndexLastX extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:index-last-x {seconds=90} {chunk=500}';

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
        $seconds = $this->intifyArgument('seconds');
        $chunk = $this->intifyArgument('chunk');
        $start->subSeconds($seconds);
        $statement_ids = Statement::query()->select(['id'])->where('created_at', '>=', $start)->pluck('id');

        $statement_ids->chunk($chunk)->each(function($bag){
            StatementIndexBag::dispatch($bag->toArray());
        });
    }
}
