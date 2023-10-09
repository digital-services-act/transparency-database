<?php

namespace App\Services;


use App\Exports\StatementExportTrait;
use App\Exports\StatementsDayExport;
use App\Jobs\MarkDayArchiveCompleted;
use App\Models\DayArchive;
use App\Models\Statement;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel;


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
    public function createDayArchive(string $date, bool $force = false): DayArchive
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d', $date);
        } catch (Exception $e) {
            throw new Exception('That date provided was not valid YYYY-MM-DD');
        }

        $today = Carbon::today();

        if ($date && $date < $today) {
            if ($force) {
                DayArchive::query()->where('date', $date->format('Y-m-d'))->delete();
            }

            $existing = $this->getDayArchiveByDate($date->format('Y-m-d'));
            if ($existing) {
                throw new Exception("A day archive for the date: " . $date->format('Y-m-d') . ' already exists.');
            }

            // Create the holding model.
            $day_archive = DayArchive::create([
                'date'  => $date->format('Y-m-d'),
                'total' => 0
            ]);

            // There needs to be a s3ds bucket.
            if (config('filesystems.disks.s3ds.bucket')) {
                // Make the url and get the total and queue.
                $file               = 'statements-of-reason-' . $date->format('Y-m-d') . '.csv';
                $url                = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $file;

                $query = Statement::query()->where('created_at', '>=', $date->format('Y-m-d') . ' 00:00:00')->where('created_at', '<=', $date->format('Y-m-d') . ' 23:59:59');

                $total              = $query->count();
                $day_archive->url   = $url;
                $day_archive->total = $total;
                $day_archive->save();

                $path = Storage::path($file);

                $csvFile = fopen($path, 'w');

                fputcsv($csvFile, $this->headings());

                // Fetch data from the database
                $statements = $query->orderBy('id', 'desc');

                $statements->each(function (Statement $statement) use ($csvFile) {
                    fputcsv($csvFile, $this->map($statement));
                });


                fclose($csvFile);

                //Storage::disk('s3ds')->move($path, $file);
                Storage::disk('s3ds')->put($file, fopen($path, 'r') );
                Storage::delete($file);

                $day_archive->completed_at = Carbon::now();
                $day_archive->save();

            }

            return $day_archive;
        } else {
            throw new Exception("When creating a day export you must supply a YYYY-MM-DD date and it needs to be in the past.");
        }
    }

    public function masterList()
    {
        return DayArchive::query()->whereNotNull('completed_at')->orderBy('date', 'DESC');
    }

    public function getDayArchiveByDate(string $date)
    {
        return DayArchive::query()->where('date', $date)->first();
    }
}