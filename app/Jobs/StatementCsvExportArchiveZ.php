<?php

namespace App\Jobs;

use App\Models\DayArchive;
use App\Models\Platform;
use App\Services\DayArchiveService;
use App\Services\StatementSearchService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 *
 * @codeCoverageIgnore
 */
class StatementCsvExportArchiveZ implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    protected $keepOldCount = false;

    public function __construct(public string $date, public string $platform_slug, public mixed $platform_id = null) {}

    private function innerZipSize($zip_file): int
    {
        $zip = new ZipArchive();
        $zip->open($zip_file);

        $totalSize = 0;

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $fileStats = $zip->statIndex($i);
            $totalSize += $fileStats['size'];
        }

        $zip->close();
        return $totalSize;
    }

    public function handle(StatementSearchService $statement_search_service, DayArchiveService $day_archive_service): void
    {
        $path = Storage::path('');
        $base_s3_url = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/';
        $date = Carbon::createFromFormat('Y-m-d', $this->date);
        $platform = Platform::find($this->platform_id);


        $csvfiles = $path . 'sor-' . $this->platform_slug . '-' . $this->date . '-full-*.csv.zip';
        $csvfilesglob = glob($csvfiles);
        $size = 0;
        foreach ($csvfilesglob as $part) {
            $size += $this->innerZipSize($part);
        }


        $csvfileslight = $path . 'sor-' . $this->platform_slug . '-' . $this->date . '-light-*.csv.zip';
        $csvfileslightglob = glob($csvfileslight);
        $sizelight = 0;
        foreach ($csvfileslightglob as $part) {
            $sizelight += $this->innerZipSize($part);
        }

        $zipfile = $path . 'sor-' . $this->platform_slug . '-' . $this->date . '-full.zip';
        $zipfilelight = $path . 'sor-' . $this->platform_slug . '-' . $this->date . '-light.zip';

        $zipfilesha1 = $path . 'sor-' . $this->platform_slug . '-' . $this->date . '-full.zip.sha1';
        $zipfilelightsha1 = $path . 'sor-' . $this->platform_slug . '-' . $this->date . '-light.zip.sha1';


        $total = 0;
        $existing = $this->platform_slug === 'global' ? $day_archive_service->getDayArchiveByDate($date) : $day_archive_service->getDayArchiveByPlatformDate($platform, $date);
        if ($existing && $this->keepOldCount) {
            $total = $existing->total;
        } else {
            $total = $this->platform_slug === 'global' ? $statement_search_service->totalForDate($date) : $statement_search_service->totalForPlatformDate($platform, $date);
            $existing?->delete();
        }

        DayArchive::updateOrInsert(
            [
                'date' => $this->date,
                'platform_id'  => $this->platform_id,
            ],
            [
                'total'        => $total,
                'url'          => $base_s3_url . basename($zipfile),
                'urllight'     => $base_s3_url . basename($zipfilelight),
                'sha1url'      => $base_s3_url . basename($zipfilesha1),
                'sha1urllight' => $base_s3_url . basename($zipfilelightsha1),
                'completed_at' => Carbon::now(),
                'size' => $size,
                'sizelight' => $sizelight,
                'zipsize' => filesize($zipfile),
                'ziplightsize' => filesize($zipfilelight),
            ]
        );
    }
}
