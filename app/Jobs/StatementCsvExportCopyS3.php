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

class StatementCsvExportCopyS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $zip;
    public string $sha1;

    public function __construct(string $zip, string $sha1)
    {
        $this->zip = $zip;
        $this->sha1 = $sha1;
    }

    public function handle(): void
    {
        $path = Storage::path('') . 's3ds/';
        shell_exec('/usr/bin/cp ' . $this->zip . ' ' . $path);
        shell_exec('/usr/bin/cp ' . $this->sha1 . ' ' . $path);
    }
}