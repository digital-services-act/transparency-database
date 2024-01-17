<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class StatementCsvExportZipPart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $part;
    public string $zip;

    public function __construct(string $part, string $zip)
    {
        $this->part = $part;
        $this->zip = $zip;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->zip))->releaseAfter(30)];
    }

    public function handle(): void
    {
        $path = Storage::path('');
        shell_exec('cd ' . $path . ';zip ' . $this->zip . ' ' . $this->part);
    }
}