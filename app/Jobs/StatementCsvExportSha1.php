<?php

namespace App\Jobs;

use App\Services\DayArchiveWorkspace;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StatementCsvExportSha1 implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public string $date, public string $platform, public string $version) {}

    public function handle(DayArchiveWorkspace $day_archive_workspace): void
    {
        $zipfile = 'sor-'.$this->platform.'-'.$this->date.'-'.$this->version.'.zip';
        $sha1 = 'sor-'.$this->platform.'-'.$this->date.'-'.$this->version.'.zip.sha1';
        $day_archive_workspace->put($sha1, sha1_file($day_archive_workspace->path($zipfile)).'  '.basename($zipfile));
    }
}
