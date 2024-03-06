<?php

namespace App\Console\Commands;

use App\Models\DayArchive;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class StatementsDayArchiveOldest extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:day-archive-oldest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a day archive compile for the oldest existing day archive.';

    /**
     * Execute the console command.
     * @throws Exception
     * @throws Throwable
     */
    public function handle(): void
    {
        $test = glob('storage/app/sor*');
        if(count($test) > 0) {
            Log::info('Oldest archiving can not run, day archive already in progress');
            return;
        }

        $oldest = DayArchive::query()->orderBy('created_at', 'asc')->first();
        if ($oldest) {
            $date = $oldest->date->format('Y-m-d');
            $this->call('statements:day-archive-z', ['date' => $date]);
        }
    }
}
