<?php

namespace App\Jobs;

use App\Services\DayArchiveWorkspace;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class StatementCsvExportCopyS3 implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public string $zip, public string $sha1) {}

    public function handle(DayArchiveWorkspace $day_archive_workspace): void
    {
        $disk = Storage::disk('s3ds');
        $zip = fopen($day_archive_workspace->path($this->zip), 'rb');
        $sha1 = fopen($day_archive_workspace->path($this->sha1), 'rb');

        try {
            $disk->put($this->zip, $zip, [
                'visibility' => 'private',
            ]);
            $disk->put($this->sha1, $sha1, [
                'visibility' => 'private',
            ]);
        } finally {
            if (is_resource($zip)) {
                fclose($zip);
            }

            if (is_resource($sha1)) {
                fclose($sha1);
            }
        }
    }
}
