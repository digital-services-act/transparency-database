<?php

namespace App\Console\Commands;

use App\Services\DayArchiveService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class StatementsDayArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:day-archive {date=yesterday} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a day archive compile job.';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle(DayArchiveService $day_archive_service)
    {
        try {
            $date = $this->argument('date');
            if ($date === 'yesterday') {
                $date = Carbon::yesterday();
            } else {
                $date = Carbon::createFromFormat('Y-m-d', $date);
            }
            $force = (bool)$this->option('force', false);
            $day_archive_service->createDayArchive($date->format('Y-m-d'), $force);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
