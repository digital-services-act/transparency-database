<?php

namespace App\Jobs;

use App\Models\Platform;
use App\Services\DayArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StatementCsvExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $date;
    public string $part;
    public int $start_id;
    public int $end_id;
    public bool $headers;

    public function __construct(string $date, string $part, int $start_id, int $end_id, bool $headers = false)
    {
        $this->date     = $date;
        $this->part     = $part;
        $this->start_id = $start_id;
        $this->end_id   = $end_id;
        $this->headers  = $headers;
    }

    public function handle(DayArchiveService $day_archive_service): void
    {
        $platforms = Platform::all()->pluck('name', 'id')->toArray();

        $exports = $day_archive_service->buildBasicArray();

        foreach ($exports as $index => $export) {
            $export['file']          = 'sor-full-' . $export['slug'] . '-' . $this->date . '-' . $this->part . '.csv';
            $export['filelight']     = 'sor-light-' . $export['slug'] . '-' . $this->date . '-' . $this->part . '.csv';
            $export['path']          = Storage::path($export['file']);
            $export['pathlight']     = Storage::path($export['filelight']);
            $export['csv_file']      = fopen($export['path'], 'wb');
            $export['csv_filelight'] = fopen($export['pathlight'], 'wb');
            if ($this->headers) {
                fputcsv($export['csv_file'], $day_archive_service->headings());
                fputcsv($export['csv_filelight'], $day_archive_service->headingsLight());
            }

            $exports[$index] = $export;
        }

        $select_raw = $day_archive_service->getSelectRawString();

        $raw = DB::table('statements')
                 ->selectRaw($select_raw)
                 ->where('statements.id', '>=', $this->start_id)
                 ->where('statements.id', '<=', $this->end_id)
                 ->orderBy('statements.id');

        $raw->chunk(100000, function (Collection $statements) use ($exports, $platforms, $day_archive_service) {
            foreach ($statements as $statement) {
                // Write to the global no matter what.
                $row      = $day_archive_service->mapRaw($statement, $platforms);
                $rowlight = $day_archive_service->mapRawLight($statement, $platforms);
                fputcsv($exports[0]['csv_file'], $row);
                fputcsv($exports[0]['csv_filelight'], $rowlight);

                // Potentially also write to the platform file
                if (isset($exports[$statement->platform_id])) {
                    fputcsv($exports[$statement->platform_id]['csv_file'], $row);
                    fputcsv($exports[$statement->platform_id]['csv_filelight'], $rowlight);
                }
            }

            // Flush
            foreach ($exports as $export) {
                fflush($export['csv_file']);
                fflush($export['csv_filelight']);
            }
        });

        foreach ($exports as $export) {
            fclose($export['csv_file']);
            fclose($export['csv_filelight']);
        }
    }
}