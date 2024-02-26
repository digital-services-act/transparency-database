<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportGroupParts implements ShouldQueue
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
        $zip_parts_pattern = $path . 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '-*.csv.zip';
        $zip_file = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.csv.zip';

        // exec zip command to just store the zip parts in a zip file.
        shell_exec('zip -0 -j ' . $path . $zip_file . ' ' . $zip_parts_pattern);
    }
}