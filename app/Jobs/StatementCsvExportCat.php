<?php

namespace App\Jobs;

use App\Services\DayArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportCat implements ShouldQueue
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

    public function handle(DayArchiveService $day_archive_service): void
    {
        $path = Storage::path('');
        $main = $path . 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.csv';
        $glob = $path . 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '-*.csv';

        shell_exec('/usr/bin/cat ' . $glob . ' > ' . $main);
        shell_exec('/usr/bin/rm ' . $glob);
    }
}