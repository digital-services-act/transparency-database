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
        if (!config('filesystems.disks.s3ds.bucket')) {
            $this->error('In order to make day archives, you need to define the "s3ds" bucket.');
            return;
        }
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

        $this->info('Usage: ' . $this->formatBytes(memory_get_peak_usage()));

    }

    public function formatBytes($bytes, $precision = 2) {
        $units = array("b", "kb", "mb", "gb", "tb");

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . " " . $units[$pow];
    }

}
