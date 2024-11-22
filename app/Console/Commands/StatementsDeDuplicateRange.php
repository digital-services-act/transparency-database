<?php

namespace App\Console\Commands;

use App\Jobs\StatementDeDuplicateRange;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @codeCoverageIgnore
 */
class StatementsDeDuplicateRange extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:deduplicate-range {min=default} {max=default} {chunk=3000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DeDuplicate the Statements based on a range';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        if (!Cache::get('deduplication-has-been-run', false)) {
            Cache::forever('deduplication-has-been-run', true);
            $chunk = $this->intifyArgument('chunk');
            $min   = $this->argument('min') === 'default' ? DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
            $max   = $this->argument('max') === 'default' ? DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');

            StatementDeDuplicateRange::dispatch($max, $min, $chunk);
        } else {
            $this->warn('This command has been run and cache should be cleared before running again.');
        }
    }
}
