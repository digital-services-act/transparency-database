<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportZipParts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(public string $date, public string $platform, public string $version)
    {
    }

    public function handle(): void
    {
        $path = Storage::path('');
        $pattern = $path . 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '-*.csv';
        $parts = glob($pattern);
        $zip_file = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.csv.zip';

        $zip = new \ZipArchive();
        $zip->open($path . $zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($parts as $part) {
            $zip->addFile($part, basename($part));
        }
        $zip->close();
    }
}