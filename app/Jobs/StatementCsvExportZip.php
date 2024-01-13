<?php

namespace App\Jobs;

use App\Services\DayArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class StatementCsvExportZip implements ShouldQueue
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
        $csv = 'storage/app/sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '.csv';
        $zip = 'storage/app/s3ds/sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '.csv.zip';
        $sha1 = 's3ds/sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '.csv.zip.sha1';
        shell_exec('zip ' . $zip . ' ' . $csv);
        Storage::put($sha1, sha1_file($zip) . "  " . basename($zip));
    }
}