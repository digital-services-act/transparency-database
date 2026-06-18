<?php

namespace App\Console\Commands;

use App\Models\DownloadEvent;
use Illuminate\Console\Command;

class PruneDownloadActivity extends Command
{
    protected $signature = 'downloads:prune-activity
        {--days= : Delete events older than this many days instead of the configured retention period}';

    protected $description = 'Delete expired download activity events.';

    public function handle(): int
    {
        $days = $this->option('days') ?? config('downloads.activity_retention_days');

        if (filter_var($days, FILTER_VALIDATE_INT) === false || (int) $days < 1) {
            $this->error('The retention period must be a positive number of days.');

            return self::FAILURE;
        }

        $deleted = DownloadEvent::query()
            ->where('created_at', '<', now()->subDays((int) $days))
            ->delete();

        $this->info("Deleted {$deleted} expired download activity event(s).");

        return self::SUCCESS;
    }
}
