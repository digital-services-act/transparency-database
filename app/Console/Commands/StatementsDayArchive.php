<?php

namespace App\Console\Commands;

use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class StatementsDayArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:day-archive {date=yesterday} {--force} {--info}';

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
        if ( ! config('filesystems.disks.s3ds.bucket')) {
            $this->error('In order to make day archives, you need to define the "s3ds" bucket.');

            return;
        }

        $date = $this->argument('date');
        if ($date === 'yesterday') {
            $date = Carbon::yesterday();
        } else {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $date);
            } catch (Exception $e) {
                $this->error('Issue with the date provided, checked the format yyyy-mm-dd');
            }
        }

        $force = (bool)$this->option('force', false);
        $info  = (bool)$this->option('info', false);

        try {
            $day_archive_service->createDayArchive($date, $force);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        if ($info) {
            $this->info('Memory Usage: ' . $this->formatBytes(memory_get_peak_usage()));
        }

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
