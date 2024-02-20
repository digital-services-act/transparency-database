<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ToggleReindexing extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toggle-reindexing {state}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle the reindexing cache value on or off.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $state = $this->boolifyArgument('state');
        Cache::forever('reindexing', $state);
    }
}
