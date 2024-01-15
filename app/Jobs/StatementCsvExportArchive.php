<?php

namespace App\Jobs;

use App\Models\DayArchive;
use App\Models\Platform;
use App\Services\DayArchiveService;
use App\Services\StatementSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class StatementCsvExportArchive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $date;
    public string $platform_slug;
    public int $platform_id;

    public function __construct(string $date, string $platform_slug, int $platform_id = null)
    {
        $this->date = $date;
        $this->platform_slug = $platform_slug;
        $this->platform_id = $platform_id;
    }

    public function handle(StatementSearchService $statement_search_service, DayArchiveService $day_archive_service): void
    {
        $path = Storage::path('');
        $base_s3_url = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/';
        $date = Carbon::createFromFormat('Y-m-d', $this->date);
        $platform = Platform::find($this->platform_id);

        $csvfile = $path . 'sor-full-' . $this->platform_slug . '-' . $this->date . '.csv';
        $csvfiles = $path . 'sor-full-' . $this->platform_slug . '-' . $this->date . '-*.csv';
        $csvfilesglob = glob($csvfiles);
        $size = 0;
        foreach ($csvfilesglob as $part) {
            $size += filesize($part);
        }

        $csvfilelight = $path . 'sor-light-' . $this->platform_slug . '-' . $this->date . '.csv';
        $csvfileslight = $path . 'sor-light-' . $this->platform_slug . '-' . $this->date . '-*.csv';
        $csvfileslightglob = glob($csvfileslight);
        $sizelight = 0;
        foreach ($csvfileslightglob as $part) {
            $sizelight += filesize($part);
        }

        $zipfile = $path . 'sor-full-' . $this->platform_slug . '-' . $this->date . '.csv.zip';
        $zipfilelight = $path . 'sor-light-' . $this->platform_slug . '-' . $this->date . '.csv.zip';

        $zipfilesha1 = $path . 'sor-full-' . $this->platform_slug . '-' . $this->date . '.csv.zip.sha1';
        $zipfilelightsha1 = $path . 'sor-light-' . $this->platform_slug . '-' . $this->date . '.csv.zip.sha1';

        $existing = $this->platform_slug === 'global' ? $day_archive_service->getDayArchiveByDate($date) : $day_archive_service->getDayArchiveByPlatformDate($platform, $date);
        $existing?->delete();

        DayArchive::create([
            'date'         => $this->date,
            'total'        => $this->platform_slug === 'global' ? $statement_search_service->totalForDate($date) : $statement_search_service->totalForPlatformDate($platform, $date),
            'platform_id'  => $this->platform_id,
            'url'          => $base_s3_url . basename($zipfile),
            'urllight'     => $base_s3_url . basename($zipfilelight),
            'sha1url'      => $base_s3_url . basename($zipfilesha1),
            'sha1urllight' => $base_s3_url . basename($zipfilelightsha1),
            'completed_at' => Carbon::now(),
            'size' => $size,
            'sizelight' => $sizelight,
            'zipsize' => filesize($zipfile),
            'ziplightsize' => filesize($zipfilelight),
        ]);
    }
}