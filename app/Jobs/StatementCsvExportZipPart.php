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

class StatementCsvExportZipPart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $csv;

    public function __construct(string $csv)
    {
        $this->csv = $csv;
    }

    public function handle(): void
    {
        $zipfile = $this->csv . '.zip';
        $zip = new ZipArchive;
        $zip->open($zipfile, ZipArchive::CREATE);
        $zip->addFile($this->csv, basename($this->csv));
        $zip->close();
    }
}