<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class ToggleStopReindexing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toggle-stop-reindexing {state=true}';

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
        Cache::forever('stop_reindexing', $this->argument('state') === 'true');
    }
}
