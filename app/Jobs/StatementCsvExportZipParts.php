<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportZipParts implements ShouldQueue
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
        $glob = $path . 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '-*.csv';
        $zip = $path . 'sor-' . $this->platform . '-' . $this->date . '-' . $this->version . '.csv.zip';
        $parts = glob($glob);
        $jobs = [];
        foreach ($parts as $part) {
            $jobs[] = new StatementCsvExportZipPart($part, $zip);
        }
        Bus::chain($jobs)->dispatch();
    }
}