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
        $csv_glob = glob('storage/app/sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '-*.csv');
        $zip = 'storage/app/sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '.csv.zip';
        $sha1 = 'sor-' . $this->version . '-' . $this->platform . '-' . $this->date . '.csv.zip.sha1';

        $zip_archive = new ZipArchive;
        if ($zip_archive->open($zip, ZipArchive::CREATE) === true) {
            foreach ($csv_glob as $csv) {
                $zip_archive->addFile($csv, basename($csv));
            }
            $zip_archive->close();
            Storage::put($sha1, sha1_file($zip) . "  " . basename($zip));
        } else {
            throw new RuntimeException('Issue with creating the zip file.');
        }
    }
}