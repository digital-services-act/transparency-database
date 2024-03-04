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

class StatementCsvExportZ implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;
    public function __construct(public string $date, public string $part, public int $start_id, public int $end_id, public bool $headers = false)
    {
    }

    private function csvstr(array $fields) : string
    {
        $f = fopen('php://memory', 'wb+');
        if (fputcsv($f, $fields) === false) {
            return false;
        }
        rewind($f);
        $csv_line = stream_get_contents($f);
        return rtrim($csv_line);
    }

    public function handle(DayArchiveService $day_archive_service): void
    {
        $platforms = Platform::all()->pluck('name', 'id')->toArray();

        $exports = $day_archive_service->buildBasicExportsArray();
        $versions = ['full', 'light'];
        $select_raw = $day_archive_service->getSelectRawString();
        $chunk = 100000;

        $current_start = $this->start_id;

        $headings = [];
        $headings['full'] = $this->csvstr($day_archive_service->headings());
        $headings['light'] = $this->csvstr($day_archive_service->headingsLight());

        foreach ($exports as $index => $export) {
            $exports[$index]['subparts']['full'] = [];
            $exports[$index]['subparts']['light'] = [];
        }

        $subpart = 0;
        while ($current_start <= $this->end_id) {

            foreach ($exports as $index => $export) {
                $exports[$index]['subparts']['full'][$subpart] = [];
                $exports[$index]['subparts']['light'][$subpart] = [];
            }


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
                $row_light = $day_archive_service->mapRawLight($statement, $platforms);

                $csv = $this->csvstr($row);
                $csv_light = $this->csvstr($row_light);

                // Always put it into global
                $exports[0]['subparts']['full'][$subpart][]  = $csv;
                $exports[0]['subparts']['light'][$subpart][] = $csv_light;

                // Maybe put it also in a lower.
                if (isset($exports[$statement->platform_id])) {
                    $exports[$statement->platform_id]['subparts']['full'][$subpart][]  = $csv;
                    $exports[$statement->platform_id]['subparts']['light'][$subpart][] = $csv_light;
                }
            }

            $subpart++;
            $current_start += $chunk + 1;
        }


        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $zip_file  = Storage::path('sor-' . $export['slug'] . '-' . $this->date . '-' . $version . '-' . $this->part . '.csv.zip');
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                foreach ($export['subparts'][$version] as $subpart => $rows) {
                    if ($subpart === 0 || count($rows)) {
                        $csv_file = 'sor-' . $export['slug'] . '-' . $this->date . '-' . $version . '-' . $this->part . '-' . sprintf('%05d', $subpart) . '.csv';
                        $zip->addFromString($csv_file, $headings[$version] . "\n" . implode("\n", $rows));
                    }
                }
                $zip->close();
            }
        }
    }
}