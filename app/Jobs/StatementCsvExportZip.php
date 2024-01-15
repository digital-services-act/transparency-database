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
        //sor-full-youtube-2023-12-09-00000.csv
        $csvs = $path . 'sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '-*.csv';
        $csvglob = glob($csvs);
        $zipfile = $path . 'sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '.csv.zip';
        $sha1 = 'sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '.csv.zip.sha1';

        $zip = new ZipArchive;
        $zip->open($zipfile, ZipArchive::CREATE);
        foreach ($csvglob as $csv) {
            $zip->addFile($csv, basename($csv));
        }
        $zip->close();

        Storage::put($sha1, sha1_file($zipfile) . "  " . basename($zipfile));
    }
}