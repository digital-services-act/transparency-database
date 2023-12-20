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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class DayArchiveService
{
    use StatementExportTrait;

    protected StatementSearchService $statement_search_service;

    public function __construct(StatementSearchService $statement_search_service)
    {
        $this->statement_search_service = $statement_search_service;
    }

    /**
     * @param Carbon $date
     * @param bool $force
     *
     * @return bool
     * @throws Exception
     */
    public function createDayArchive(Carbon $date, bool $force = false): bool
    {
        $today = Carbon::today();

        if ($date < $today) {
            $existing = $this->getDayArchivesByDate($date);
            if ($existing->count()) {
                if ($force) {
                    $existing->delete();
                } else {
                    throw new RuntimeException("A day archive for the date: " . $date->format('Y-m-d') . ' already exists.');
                }
            }

            // There needs to be a s3ds bucket.
            if (config('filesystems.disks.s3ds.bucket')) {

                $platforms = Platform::all()->pluck('name', 'id')->toArray();
                $day_archives = $this->buildStartingDayArchivesArray($date);

                $this->startAllCsvFiles($day_archives);
                $raw = $this->getRawQuery($date);
                $this->chunkAndWrite($raw, $day_archives, $platforms);
                $this->closeAllCsvFiles($day_archives);
                $this->generateZipsSha1sAndUpdate($day_archives);

                if (!Storage::exists('s3ds/mounted.txt')) {
                    $this->uploadTheZipsAndSha1s($day_archives);
                    $this->cleanUpZipAndSha1Files($day_archives);
                }

                $this->cleanUpCsvFiles($day_archives);
                $this->markArchivesComplete($day_archives);

            } else {
                throw new RuntimeException("Day archives have to be uploaded to a dedicated s3ds disk. please be sure that there is one to write to.");
            }

            return true;
        }

        throw new RuntimeException("When creating a day export you must supply a date in the past.");
    }

    public function recoverUpload(Carbon $date): bool
    {
        $existing = $this->getDayArchivesByDate($date);
        if ($existing->count()) {
            if (config('filesystems.disks.s3ds.bucket')) {
                $day_archives = $this->buildStartingDayArchivesArray($date, true);
                $this->uploadTheZipsAndSha1s($day_archives);
                $this->cleanUpCsvFiles($day_archives);
                $this->cleanUpZipAndSha1Files($day_archives);
                $this->markArchivesComplete($day_archives);

            } else {
                throw new RuntimeException("Day archives have to be uploaded to a dedicated s3ds disk. please be sure that there is one to write to.");
            }
        }
        return true;
    }


    public function markArchivesComplete($day_archives): void
    {
        foreach ($day_archives as $day_archive) {
            $day_archive['model']->completed_at = Carbon::now();
            $day_archive['model']->save();
        }
    }

    public function cleanUpZipAndSha1Files($day_archives): void
    {
        foreach ($day_archives as $day_archive) {
            // Clean up the files.
            Storage::delete($day_archive['zipfile']);
            Storage::delete($day_archive['zipfilelight']);
            Storage::delete($day_archive['zipfilesha1']);
            Storage::delete($day_archive['zipfilelightsha1']);
        }
    }

    public function cleanUpCsvFiles($day_archives): void
    {
        foreach ($day_archives as $day_archive) {
            // Clean up the files.
            Storage::delete($day_archive['file']);
            Storage::delete($day_archive['filelight']);
        }
    }

    public function uploadTheZipsAndSha1s($day_archives): void
    {
        foreach ($day_archives as $day_archive) {
            // Put them on the s3
            Storage::disk('s3ds')->put($day_archive['zipfile'], fopen($day_archive['zippath'], 'rb'));
            Storage::disk('s3ds')->put($day_archive['zipfilelight'], fopen($day_archive['zippathlight'], 'rb'));
            Storage::disk('s3ds')->put($day_archive['zipfilesha1'], fopen($day_archive['zippathsha1'], 'rb'));
            Storage::disk('s3ds')->put($day_archive['zipfilelightsha1'], fopen($day_archive['zippathlightsha1'], 'rb'));
        }
    }

    public function generateZipsSha1sAndUpdate($day_archives): void
    {
        // Do we have the s3ds mounted?
        $s3ds = '';
        if (Storage::exists('s3ds/mounted.txt')) {
            $s3ds = 's3ds/';
        }

        foreach ($day_archives as $day_archive)
        {
            $zip = new ZipArchive;
            if ($zip->open($day_archive['zippath'], ZipArchive::CREATE) === true) {
                $zip->addFile($day_archive['path'], $day_archive['file']);
                $zip->close();
                $day_archive['model']->zipsize = filesize($day_archive['zippath']);
                $day_archive['model']->sha1 = sha1_file($day_archive['zippath']);
                Storage::put($s3ds . $day_archive['zipfilesha1'], $day_archive['model']->sha1 . "  " . $day_archive['zipfile']);
            } else {
                throw new RuntimeException('Issue with creating the zip file.');
            }
            $day_archive['model']->save();

            $ziplight = new ZipArchive;
            if ($ziplight->open($day_archive['zippathlight'], ZipArchive::CREATE) === true) {
                $ziplight->addFile($day_archive['pathlight'], $day_archive['filelight']);
                $ziplight->close();
                $day_archive['model']->ziplightsize = filesize($day_archive['zippathlight']);
                $day_archive['model']->sha1light = sha1_file($day_archive['zippathlight']);
                Storage::put($s3ds . $day_archive['zipfilelightsha1'], $day_archive['model']->sha1light . "  " . $day_archive['zipfilelight']);
            } else {
                throw new RuntimeException('Issue with creating the zip file.');
            }
            $day_archive['model']->save();
        }
    }

    public function closeAllCsvFiles($day_archives): void
    {
        foreach ($day_archives as $day_archive) {
            fclose($day_archive['csv_file']);
            fclose($day_archive['csv_filelight']);

            $day_archive['model']->size = filesize($day_archive['path']);
            $day_archive['model']->sizelight = filesize($day_archive['pathlight']);
            $day_archive['model']->save();
        }
    }

    /**
     * @param Carbon $date
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getRawQuery(Carbon $date): \Illuminate\Database\Query\Builder
    {
        $first_id = $this->getFirstIdOfDate($date);
        $last_id  = $this->getLastIdOfDate($date);

        $select_raw = $this->getSelectRawString();

        // There is no id to base off so we fall back to this query.
        if (!$first_id || !$last_id) {
            Log::debug('There was no first or last id to base the day archives query from, so we fell back to the slow query');
            $raw                = DB::table('statements')
                                    ->selectRaw($select_raw)
                                    ->where('statements.created_at', '>=', $date->format('Y-m-d') . ' 00:00:00')
                                    ->where('statements.created_at', '<=', $date->format('Y-m-d') . ' 23:59:59')
                                    ->orderBy('statements.id', 'desc');

        } else {
            $raw                = DB::table('statements')
                                    ->selectRaw($select_raw)
                                    ->where('statements.id', '>=', $first_id)
                                    ->where('statements.id', '<=', $last_id)
                                    ->orderBy('statements.id');
        }

        return $raw;
    }

    public function startAllCsvFiles(&$day_archives): void
    {
        foreach ($day_archives as $index => $day_archive) {
            $day_archive['csv_file'] = fopen($day_archive['path'], 'wb');
            $day_archive['csv_filelight'] = fopen($day_archive['pathlight'], 'wb');
            fputcsv($day_archive['csv_file'], $this->headings());
            fputcsv($day_archive['csv_filelight'], $this->headingsLight());
            $day_archives[$index] = $day_archive;
        }
    }

    public function chunkAndWrite($raw, $day_archives, $platforms): void
    {
        $raw->chunk(1000000, function (Collection $statements) use ($day_archives, $platforms) {
            foreach ($statements as $statement) {
                // Write to the global no matter what.
                $row = $this->mapRaw($statement, $platforms);
                $rowlight = $this->mapRawLight($statement, $platforms);
                fputcsv($day_archives[0]['csv_file'], $row);
                fputcsv($day_archives[0]['csv_filelight'], $rowlight);

                // Potentially also write to the platform file
                if (isset($day_archives[$statement->platform_id])) {
                    fputcsv($day_archives[$statement->platform_id]['csv_file'], $row);
                    fputcsv($day_archives[$statement->platform_id]['csv_filelight'], $rowlight);
                }
            }

            // Flush
            foreach ($day_archives as $day_archive) {
                fflush($day_archive['csv_file']);
                fflush($day_archive['csv_filelight']);
            }
        });
    }


    public function buildStartingDayArchivesArray(Carbon $date, bool $existing = false): array
    {
        $day_archives = [];

        $global = [
            'slug' => 'global',
            'id' => null,
        ];
        $day_archives[] = $global;

        $vlops = Platform::Vlops()->get();

        foreach($vlops as $vlop) {
            $day_archive = [
                'slug' => $vlop->slugifyName(),
                'id' => $vlop->id
            ];
            $day_archives[$vlop->id] = $day_archive;
        }

        // Do we have the s3ds mounted?
        $s3ds = '';
        if (Storage::exists('s3ds/mounted.txt')) {
            $s3ds = 's3ds/';
        }

        $base_s3_url = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/';

        foreach ($day_archives as $index => $day_archive) {
            $day_archive['file']             = 'sor-' . $day_archive['slug'] . '-' . $date->format('Y-m-d') . '-full.csv';
            $day_archive['filelight']        = 'sor-' . $day_archive['slug'] . '-' . $date->format('Y-m-d') . '-light.csv';
            $day_archive['path']             = Storage::path($day_archive['file']);
            $day_archive['pathlight']        = Storage::path($day_archive['filelight']);
            $day_archive['zipfile']          = $day_archive['file'] . '.zip';
            $day_archive['zipfilelight']     = $day_archive['filelight'] . '.zip';
            $day_archive['zipfilesha1']      = $day_archive['file'] . '.zip.sha1';
            $day_archive['zipfilelightsha1'] = $day_archive['filelight'] . '.zip.sha1';
            $day_archive['zippath']          = Storage::path($s3ds . $day_archive['zipfile']);
            $day_archive['zippathlight']     = Storage::path($s3ds . $day_archive['zipfilelight']);
            $day_archive['zippathsha1']      = Storage::path($s3ds . $day_archive['zipfilesha1']);
            $day_archive['zippathlightsha1'] = Storage::path($s3ds . $day_archive['zipfilelightsha1']);
            $day_archive['url']              = $base_s3_url . $day_archive['zipfile'];
            $day_archive['urllight']         = $base_s3_url . $day_archive['zipfilelight'];
            $day_archive['sha1url']          = $base_s3_url . $day_archive['zipfilesha1'];
            $day_archive['sha1urllight']     = $base_s3_url . $day_archive['zipfilelightsha1'];

            $platform = Platform::find($day_archive['id']); // can be null

            if (!$existing) {
                $model = DayArchive::create([
                    'date'         => $date,
                    'total'        => $day_archive['slug'] === 'global' ? $this->statement_search_service->totalForDate($date) : $this->statement_search_service->totalForPlatformDate($platform, $date),
                    'platform_id'  => $day_archive['id'],
                    'url'          => $day_archive['url'],
                    'urllight'     => $day_archive['urllight'],
                    'sha1url'      => $day_archive['sha1url'],
                    'sha1urllight' => $day_archive['sha1urllight'],
                ]);
            } else {
                $model = $day_archive['slug'] === 'global' ? $this->getDayArchiveByDate($date) : $this->getDayArchiveByPlatformDate($platform, $date);
            }

            if (!$model) {
                throw new RuntimeException('Day Archive model is null');
            }

            $day_archive['model'] = $model;

            $day_archives[$index] = $day_archive;
        }

        return $day_archives;
    }

    public function getFirstIdOfDate(Carbon $date)
    {
        $in = $this->buildStartOfDateArray($date);

        $first = DB::table('statements')
                   ->select('id')
                   ->whereIn('statements.created_at', $in)
                   ->orderBy('statements.id')->limit(1)->first();

        return $first->id ?? 0;
    }

    public function buildStartOfDateArray(Carbon $date): array
    {
        $date->hour   = 0;
        $date->minute = 0;
        $date->second = 0;

        $attempts_allowed = 100;

        $in = [];
        while($attempts_allowed-- > 0)
        {
            $in[] = $date->format('Y-m-d H:i:s');
            $date->addSecond();
        }
        return $in;
    }

    public function getLastIdOfDate(Carbon $date)
    {
        $in = $this->buildEndOfDateArray($date);

        $last = DB::table('statements')
                  ->select('id')
                  ->whereIn('statements.created_at', $in)
                  ->orderBy('statements.id', 'desc')->limit(1)->first();


        return $last->id ?? 0;
    }

    public function buildEndOfDateArray(Carbon $date): array
    {
        $date->hour   = 23;
        $date->minute = 59;
        $date->second = 59;

        $attempts_allowed = 100;

        $in = [];
        while($attempts_allowed-- > 0)
        {
            $in[] = $date->format('Y-m-d H:i:s');
            $date->subSecond();
        }
        return $in;
    }


    public function globalList(): Builder
    {
        return DayArchive::query()->whereNull('platform_id')->whereNotNull('completed_at')->orderBy('date', 'DESC');
    }

    public function platformList(Platform $platform): Builder
    {
        return DayArchive::query()->where('platform_id', $platform->id)->whereNotNull('completed_at')->orderBy('date', 'DESC');
    }

    /**
     * @param Carbon $date
     *
     * @return DayArchive|Model|Builder|null
     */
    public function getDayArchiveByDate(Carbon $date): DayArchive|Model|Builder|null
    {
        return DayArchive::query()->whereDate('date', $date)->whereNull('platform_id')->first();
    }

    /**
     * @param Platform $platform
     * @param Carbon $date
     *
     * @return DayArchive|Model|Builder|null
     */
    public function getDayArchiveByPlatformDate(Platform $platform, Carbon $date): DayArchive|Model|Builder|null
    {
        return DayArchive::query()->whereDate('date', $date)->where('platform_id', $platform->id)->first();
    }

    /**
     * @param Carbon $date
     *
     * @return Builder
     */
    public function getDayArchivesByDate(Carbon $date): Builder
    {
        return DayArchive::query()->whereDate('date', $date);
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