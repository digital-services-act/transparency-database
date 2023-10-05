<?php

namespace App\Services;


use App\Exports\StatementsDayExport;
use App\Jobs\MarkDayArchiveCompleted;
use App\Models\DayArchive;
use Exception;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel;


class DayArchiveService
{
    /**
     * @throws Exception
     */
    public function createDayArchive(string $date, bool $force = false)
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);
        $today = Carbon::today();

        if ($date && $date < $today) {

            if ($force) {
                DayArchive::query()->where('date', $date->format('Y-m-d'))->delete();
            }

            $existing = DayArchive::query()->where('date', $date->format('Y-m-d'))->first();
            if ($existing) {
                throw new Exception("A day archive for the date: " . $date->format('Y-m-d') . ' already exists.');
            }

            // Create the file and url
            $file = 'statements-' . $date->format('Y-m-d') . '.csv';
            $url = 'https://'. config('filesystems.disks.s3ds.bucket') .'.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $file;


            // Create the holding model.
            $day_archive = DayArchive::create([
                'date' => $date,
                'url' => $url
            ]);

            // Queue the export and chain the complete.
            (new StatementsDayExport($date->format('Y-m-d')))->queue($file, 's3ds', Excel::CSV)->chain([
                new MarkDayArchiveCompleted($day_archive->id),
            ]);


        } else {
            throw new Exception("When creating a day export you must supply a YYYY-MM-DD date and it needs to be in the past.");
        }
    }
}