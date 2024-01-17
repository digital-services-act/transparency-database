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
        $pattern = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '-*.csv';
        $zip = 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.csv.zip';
        shell_exec('cd ' . $path . ';zip ' . $zip . ' ' . $pattern);
    }
}