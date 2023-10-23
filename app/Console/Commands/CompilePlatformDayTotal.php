<?php

namespace App\Console\Commands;

use App\Models\Platform;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompilePlatformDayTotal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:compile-day-total {platform_id} {date} {attribute=all} {value=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a day total compile job.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $platform_id = (int)$this->argument('platform_id');
        $date = $this->argument('date');
        $attribute = $this->argument('attribute') !== 'all' ?  $this->argument('attribute') : '*';
        $value = $this->argument('value') !== 'all' ? $this->argument('value') : '*';

        $platform = Platform::find($platform_id);
        $date = Carbon::createFromFormat('Y-m-d', $date);
        if ($platform && $date) {
            \App\Jobs\CompilePlatformDayTotal::dispatch($platform_id, $date, $attribute, $value);
        } else {
            $this->warn('The platform id or date were invalid.');
        }
    }
}
