<?php

namespace App\Jobs;

use App\Services\DayArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportReduce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $date;
    public string $platform;
    public string $version;

    public function __construct(string $date, string $platform, string $version)
    {
        $this->date = $date;
        $this->platform = $platform;
        $this->version = $version;
    }

    public function handle(): void
    {
        $path = Storage::path('');
        $glob = $path . 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '-*.csv';
        $parts = glob($glob);
        foreach ($parts as $part) {
            if (filesize($part) === 0) {
                shell_exec('rm ' . $part);
            }
        }

        $parts_left = glob($glob);
        $count = 0;
        foreach ($parts_left as $part_left) {
            $new_name = $path . 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '-' . sprintf('%05d', $count) . '.csv';
            if ($part_left !== $new_name) {
                shell_exec('mv '. $part_left . ' ' . $new_name);
            }
            $count++;
        }

    }
}