<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportSha1 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

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
        $zipfile = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.csv.zip';
        $sha1 = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.csv.zip.sha1';
        Storage::put($sha1, sha1_file($path . $zipfile) . "  " . basename($zipfile));
    }
}