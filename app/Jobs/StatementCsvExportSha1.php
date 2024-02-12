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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;
    public function __construct(public string $date, public string $platform, public string $version)
    {
    }

    public function handle(): void
    {
        $path = Storage::path('');
        $zipfile = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.csv.zip';
        $sha1 = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.csv.zip.sha1';
        Storage::put($sha1, sha1_file($path . $zipfile) . "  " . basename($zipfile));
    }
}