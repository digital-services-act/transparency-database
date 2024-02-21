<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportZipPart implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;
    public function __construct(public string $date, public string $platform, public string $version, public string $part)
    {
    }

    public function handle(): void
    {
        $path = Storage::path('');
        $csv = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '-' . $this->part . '.csv';
        $zip_file = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '-' . $this->part . '.csv.zip';

        if (Storage::exists($csv)) {
            $zip = new \ZipArchive();
            $zip->open($path . $zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $zip->addFile($path . $csv, $csv);
            $zip->close();
        }
    }
}