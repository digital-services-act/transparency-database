<?php

namespace App\Jobs;

use App\Models\Platform;
use App\Services\DayArchiveService;
use Illuminate\Bus\Batchable;
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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;
    public function __construct(public string $date, public string $part, public int $start_id, public int $end_id, public bool $headers = false)
    {
    }

    public function handle(DayArchiveService $day_archive_service): void
    {
        $platforms = Platform::all()->pluck('name', 'id')->toArray();

        $exports = $day_archive_service->buildBasicExportsArray();

        foreach ($exports as $index => $export) {
            $export['file']          = 'sor-' . $export['slug'] . '-' . $this->date . '-full-' . $this->part . '.csv';
            $export['filelight']     = 'sor-' . $export['slug'] . '-' . $this->date . '-light-' . $this->part . '.csv';
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

        $chunk = 100000;
        $current_start = $this->start_id;


        while ($current_start <= $this->end_id) {
            $current_end = min( ($current_start + $chunk), $this->end_id );
            $statements = DB::connection('mysql::read')->table('statements')
                     ->selectRaw($select_raw)
                     ->where('statements.id', '>=', $current_start)
                     ->where('statements.id', '<=', $current_end)
                     ->orderBy('statements.id')
                     ->get();

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

            $current_start += $chunk + 1;
        }


        foreach ($exports as $export) {
            fclose($export['csv_file']);
            fclose($export['csv_filelight']);
        }
    }
}