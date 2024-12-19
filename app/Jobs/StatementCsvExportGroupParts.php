<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * 
 * @codeCoverageIgnore
 */
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
        $zip_file = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.zip';

        // Shell execs detach from the laravel job so let's do this in PHP.
        // exec zip command to just store the zip parts in a zip file.
        // shell_exec('zip -0 -j ' . $path . $zip_file . ' ' . $zip_parts_pattern);


        $zip = new ZipArchive();
        $zip->open($path . $zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addGlob($zip_parts_pattern, 0, [
            'remove_all_path' => true,
            'comp_method' => ZipArchive::CM_STORE
        ]);
        $zip->close();

    }
}