<?php

namespace App\Services;


use App\Exports\StatementExportTrait;
use App\Models\DayArchive;
use App\Models\Platform;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;


class DayArchiveService
{
    use StatementExportTrait;

    /**
     * @param string $date
     * @param bool $force
     *
     * @return DayArchive
     * @throws Exception
     */
    public function createDayArchive(Carbon $date, bool $force = false): DayArchive
    {

        $today = Carbon::today();

        if ($date < $today) {

            $existing = $this->getDayArchiveByDate($date);
            if ($existing) {
                if ($force) {
                    $existing->delete();
                } else {
                    throw new Exception("A day archive for the date: " . $date->format('Y-m-d') . ' already exists.');
                }
            }

            // Create the holding model.
            $day_archive = DayArchive::create([
                'date'  => $date->format('Y-m-d'),
                'total' => 0
            ]);

            // There needs to be a s3ds bucket.
            if (config('filesystems.disks.s3ds.bucket')) {
                // Make the url and get the total and queue.
                $file               = 'sor-' . $date->format('Y-m-d') . '-full.csv';
                $filelight               = 'sor-' . $date->format('Y-m-d') . '-light.csv';

                $path = Storage::path($file);
                $pathlight = Storage::path($filelight);

                $zipfile               = $file . '.zip';
                $zipfilelight               = $filelight . '.zip';

                $zippath = Storage::path($zipfile);
                $zippathlight = Storage::path($zipfilelight);

                $url                = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $zipfile;
                $urllight                = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $zipfilelight;


                $platforms = Platform::all()->pluck('name', 'id')->toArray();

                $raw = DB::table('statements')
                    ->where('statements.created_at', '>=', $date->format('Y-m-d') . ' 00:00:00')
                    ->where('statements.created_at', '<=', $date->format('Y-m-d') . ' 23:59:59')
                    ->orderBy('statements.id', 'desc');


                $day_archive->url   = $url;
                $day_archive->urllight   = $urllight;
                $day_archive->total = $raw->count();;
                $day_archive->save();


                $csv_file = fopen($path, 'w');
                $csv_filelight = fopen($pathlight, 'w');

                fputcsv($csv_file, $this->headings());
                fputcsv($csv_filelight, $this->headingsLight());

                $raw->chunk(100000, function(Collection $statements) use ($csv_file, $csv_filelight, $platforms) {
                    foreach ($statements as $statement) {
                        fputcsv($csv_file, $this->mapRaw($statement, $platforms));
                        fputcsv($csv_filelight, $this->mapRawLight($statement, $platforms));
                    }
                });

                fclose($csv_file);
                fclose($csv_filelight);



                $day_archive->size = Storage::size($file);
                $day_archive->sizelight = Storage::size($filelight);

                $zip = new ZipArchive;

                if ($zip->open($zippath, ZipArchive::CREATE) === TRUE)
                {
                    $zip->addFile($path, $file);
                    $zip->close();
                } else {
                    throw new Exception('Issue with creating the zip file.');
                }

                $ziplight = new ZipArchive;

                if ($ziplight->open($zippathlight, ZipArchive::CREATE) === TRUE)
                {
                    $ziplight->addFile($pathlight, $filelight);
                    $ziplight->close();
                } else {
                    throw new Exception('Issue with creating the zip light file.');
                }

                Storage::disk('s3ds')->put($zipfile, fopen($zippath, 'r+') );
                Storage::disk('s3ds')->put($zipfilelight, fopen($zippathlight, 'r+') );
                Storage::delete($file);
                Storage::delete($filelight);
                Storage::delete($zipfile);
                Storage::delete($zipfilelight);

                $day_archive->completed_at = Carbon::now();
                $day_archive->save();

            } else {
                throw new Exception("Day archives have to be upload to a dedicated s3ds disk. please sure that there is one to write to.");
            }

            return $day_archive;
        } else {
            throw new Exception("When creating a day export you must supply a date in the past.");
        }
    }

    public function masterList()
    {
        return DayArchive::query()->whereNotNull('completed_at')->orderBy('date', 'DESC');
    }

    public function getDayArchiveByDate(Carbon $date)
    {
        return DayArchive::query()->where('date', $date->format('Y-m-d'))->first();
    }
}