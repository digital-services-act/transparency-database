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
        $path = Storage::path('');


        $parts = $path . 'sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '-*.csv.zip';
        $partsglob = glob($parts);
        $zipfile = $path . 'sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '.csv.zip';
        $sha1 = 'sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '.csv.zip.sha1';

        $zip = new ZipArchive;
        $zip->open($zipfile, ZipArchive::CREATE);
        foreach ($partsglob as $part) {
            $zip->addFile($part, basename($part), 0, 0, ZipArchive::CM_STORE);
        }
        $zip->close();
        Storage::put($sha1, sha1_file($zipfile) . "  " . basename($zipfile));
    }
}