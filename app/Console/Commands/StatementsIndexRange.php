<?php

namespace App\Console\Commands;

use App\Jobs\StatementIndexRange;
use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StatementsIndexRange extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:index-range {min=default} {max=default} {chunk=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index the Statements based on a range';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $chunk = $this->intifyArgument('chunk');
        $min = $this->argument('min') === 'default' ? DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
        $max = $this->argument('max') === 'default' ? DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');

        StatementIndexRange::dispatch($max, $min, $chunk);
    }
}
