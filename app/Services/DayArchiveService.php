<?php

namespace App\Services;


use App\Exports\StatementExportTrait;
use App\Models\DayArchive;
use App\Models\Platform;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;


class DayArchiveService
{
    use StatementExportTrait;

    /**
     * @param Carbon $date
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
                    throw new RuntimeException("A day archive for the date: " . $date->format('Y-m-d') . ' already exists.');
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
                $file      = 'sor-' . $date->format('Y-m-d') . '-full.csv';
                $filelight = 'sor-' . $date->format('Y-m-d') . '-light.csv';

                $path      = Storage::path($file);
                $pathlight = Storage::path($filelight);

                $zipfile      = $file . '.zip';
                $zipfilelight = $filelight . '.zip';

                $zippath      = Storage::path($zipfile);
                $zippathlight = Storage::path($zipfilelight);

                $url      = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $zipfile;
                $urllight = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $zipfilelight;


                $platforms = Platform::all()->pluck('name', 'id')->toArray();

                $first_id = $this->getFirstIdOfDate($date);
                $last_id  = $this->getLastIdOfDate($date);

                $csv_file      = fopen($path, 'wb');
                $csv_filelight = fopen($pathlight, 'wb');

                $day_archive->url      = $url;
                $day_archive->urllight = $urllight;
                $day_archive->total    = app(PlatformDayTotalsService::class)->globalTotalForDate($date);
                $day_archive->save();

                fputcsv($csv_file, $this->headings());
                fputcsv($csv_filelight, $this->headingsLight());

                $select_raw = $this->getSelectRawString();

                $raw                = DB::table('statements')
                                        ->selectRaw($select_raw)
                                        ->where('statements.id', '>=', $first_id)
                                        ->where('statements.id', '<=', $last_id)
                                        ->orderBy('statements.id');


                if ( ! $first_id || ! $last_id) {
                    $raw                = DB::table('statements')
                                            ->selectRaw($select_raw)
                                            ->where('statements.created_at', '>=', $date->format('Y-m-d') . ' 00:00:00')
                                            ->where('statements.created_at', '<=', $date->format('Y-m-d') . ' 23:59:59')
                                            ->orderBy('statements.id', 'desc');

                }

                $day_archive->save();


                $raw->chunk(1000000, function (Collection $statements) use ($csv_file, $csv_filelight, $platforms) {
                    foreach ($statements as $statement) {
                        $row = $this->mapRaw($statement, $platforms);
                        fputcsv($csv_file, $row);

                        $row = $this->mapRawLight($statement, $platforms);
                        fputcsv($csv_filelight, $row);
                    }
                });

                fclose($csv_file);
                fclose($csv_filelight);


                $zip = new ZipArchive;
                if ($zip->open($zippath, ZipArchive::CREATE) === true) {
                    $zip->addFile($path, $file);
                    $zip->close();
                    $day_archive->size = filesize($zippath);
                } else {
                    throw new RuntimeException('Issue with creating the zip file.');
                }
                $day_archive->save();

                $ziplight = new ZipArchive;
                if ($ziplight->open($zippathlight, ZipArchive::CREATE) === true) {
                    $ziplight->addFile($pathlight, $filelight);
                    $ziplight->close();
                    $day_archive->sizelight = filesize($zippathlight);
                } else {
                    throw new RuntimeException('Issue with creating the zip light file.');
                }
                $day_archive->save();

                // Put them on the s3
                Storage::disk('s3ds')->put($zipfile, fopen($zippath, 'rb'));
                Storage::disk('s3ds')->put($zipfilelight, fopen($zippathlight, 'rb'));


                // Clean up the files.
                Storage::delete($file);
                Storage::delete($filelight);
                Storage::delete($zipfile);
                Storage::delete($zipfilelight);

                $day_archive->completed_at = Carbon::now();
                $day_archive->save();
            } else {
                throw new RuntimeException("Day archives have to be upload to a dedicated s3ds disk. please sure that there is one to write to.");
            }

            return $day_archive;
        }

        throw new RuntimeException("When creating a day export you must supply a date in the past.");
    }

    public function getFirstIdOfDate(Carbon $date)
    {
        $first        = null;
        $date->hour   = 0;
        $date->minute = 0;
        $date->second = 0;

        $attempts_allowed = 100;

        while ( ! $first && $attempts_allowed--) {
            $first = DB::table('statements')
                       ->select('id')
                       ->where('statements.created_at', '=', $date->format('Y-m-d H:i:s'))
                       ->orderBy('statements.id')->first();
            $date->addSecond();
        }

        return $first->id ?? 0;
    }

    public function getLastIdOfDate(Carbon $date)
    {
        $last         = null;
        $date->hour   = 23;
        $date->minute = 59;
        $date->second = 59;

        $attempts_allowed = 100;

        while ( ! $last && $attempts_allowed--) {
            $last = DB::table('statements')
                      ->select('id')
                      ->where('statements.created_at', '=', $date->format('Y-m-d H:i:s'))
                      ->orderBy('statements.id', 'desc')->first();
            $date->subSecond();
        }

        return $last->id ?? 0;
    }


    public function masterList()
    {
        return DayArchive::query()->whereNotNull('completed_at')->orderBy('date', 'DESC');
    }

    /**
     * @param Carbon $date
     *
     * @return DayArchive|Model|Builder|null
     */
    public function getDayArchiveByDate(Carbon $date): DayArchive|Model|Builder|null
    {
        return DayArchive::query()->where('date', $date->format('Y-m-d'))->first();
    }

    private function getSelectRawString(): string
    {
        $selects   = [];
        $selects[] = "uuid";
        $selects[] = "decision_visibility";
        $selects[] = "REPLACE(decision_visibility_other, '\n', ' ') AS decision_visibility_other";
        $selects[] = "end_date_visibility_restriction";

        $selects[] = "decision_monetary";
        $selects[] = "REPLACE(decision_monetary_other, '\n', ' ') AS decision_monetary_other";
        $selects[] = "end_date_monetary_restriction";

        $selects[] = "decision_provision";
        $selects[] = "end_date_service_restriction";

        $selects[] = "decision_account";
        $selects[] = "end_date_account_restriction";
        $selects[] = "account_type";

        $selects[] = "decision_ground";
        $selects[] = "REPLACE(decision_ground_reference_url, '\n',' ') AS decision_ground_reference_url";

        $selects[] = "REPLACE(illegal_content_legal_ground, '\n',' ') AS illegal_content_legal_ground";
        $selects[] = "REPLACE(illegal_content_explanation, '\n',' ') AS illegal_content_explanation";
        $selects[] = "REPLACE(incompatible_content_ground, '\n',' ') AS incompatible_content_ground";
        $selects[] = "REPLACE(incompatible_content_explanation, '\n',' ') AS incompatible_content_explanation";
        $selects[] = "incompatible_content_illegal";

        $selects[] = "category";
        $selects[] = "category_addition";
        $selects[] = "category_specification";
        $selects[] = "REPLACE(category_specification_other, '\n',' ') AS category_specification_other";

        $selects[] = "content_type";
        $selects[] = "REPLACE(content_type_other, '\n',' ') AS content_type_other";
        $selects[] = "content_language";
        $selects[] = "content_date";

        $selects[] = "territorial_scope";
        $selects[] = "application_date";
        $selects[] = "REPLACE(decision_facts, '\n',' ') AS decision_facts";

        $selects[] = "source_type";
        $selects[] = "REPLACE(source_identity, '\n',' ') AS source_identity";

        $selects[] = "automated_detection";
        $selects[] = "automated_decision";

        $selects[] = "REPLACE(puid, '\n',' ') AS puid";
        $selects[] = "created_at";
        $selects[] = "platform_id";

        return implode(", ", $selects);
    }
}