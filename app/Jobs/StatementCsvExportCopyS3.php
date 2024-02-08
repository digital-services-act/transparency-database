<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportCopyS3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(public string $zip, public string $sha1)
    {
    }

    public function handle(): void
    {
        $path = Storage::path('');
        $disk = Storage::disk('s3ds');
        $disk->put($this->zip, fopen($path . $this->zip, 'r+'));
        $disk->put($this->sha1, fopen($path . $this->sha1, 'r+'));
    }
}