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
        /** @var PlatformDayTotalsService $platform_day_total_service */
        $platform_day_total_service = app(PlatformDayTotalsService::class);

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


                foreach ($day_archives as $index => $day_archive) {
                    $day_archive['file'] = 'sor-' . $day_archive['slug'] . '-' . $date->format('Y-m-d') . '-full.csv';
                    $day_archive['filelight'] = 'sor-' . $day_archive['slug'] . '-' . $date->format('Y-m-d') . '-light.csv';
                    $day_archive['path'] = Storage::path($day_archive['file']);
                    $day_archive['pathlight'] = Storage::path($day_archive['filelight']);
                    $day_archive['zipfile'] = $day_archive['file'] . '.zip';
                    $day_archive['zipfilelight'] = $day_archive['filelight'] . '.zip';
                    $day_archive['zipfilesha1'] = $day_archive['file'] . '.zip.sha1';
                    $day_archive['zipfilelightsha1'] = $day_archive['filelight'] . '.zip.sha1';
                    $day_archive['zippath'] = Storage::path($day_archive['zipfile']);
                    $day_archive['zippathlight'] = Storage::path($day_archive['zipfilelight']);
                    $day_archive['zippathsha1'] = Storage::path($day_archive['zipfilesha1']);
                    $day_archive['zippathlightsha1'] = Storage::path($day_archive['zipfilelightsha1']);
                    $day_archive['url']      = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $day_archive['zipfile'];
                    $day_archive['urllight']      = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $day_archive['zipfilelight'];
                    $day_archive['sha1url']      = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $day_archive['zipfilesha1'];
                    $day_archive['sha1urllight']      = 'https://' . config('filesystems.disks.s3ds.bucket') . '.s3.' . config('filesystems.disks.s3ds.region') . '.amazonaws.com/' . $day_archive['zipfilelightsha1'];

                    $platform = Platform::find($day_archive['id']);

                    $model = DayArchive::create([
                        'date'  => $date->format('Y-m-d'),
                        'total' => $day_archive['slug'] === 'global' ? $platform_day_total_service->globalTotalForDate($date) : $platform_day_total_service->getDayTotal($platform, $date),
                        'platform_id' => $day_archive['id'],
                        'url' => $day_archive['url'],
                        'urllight' => $day_archive['urllight'],
                        'sha1url' => $day_archive['sha1url'],
                        'sha1urllight' => $day_archive['sha1urllight'],
                    ]);

                    $day_archive['model'] = $model;
                    $day_archive['csv_file'] = fopen($day_archive['path'], 'wb');
                    $day_archive['csv_filelight'] = fopen($day_archive['pathlight'], 'wb');

                    fputcsv($day_archive['csv_file'], $this->headings());
                    fputcsv($day_archive['csv_filelight'], $this->headingsLight());

                    $day_archives[$index] = $day_archive;
                }

                $platforms = Platform::all()->pluck('name', 'id')->toArray();


                $first_id = $this->getFirstIdOfDate($date);
                $last_id  = $this->getLastIdOfDate($date);

                $select_raw = $this->getSelectRawString();

                $raw                = DB::table('statements')
                                        ->selectRaw($select_raw)
                                        ->where('statements.id', '>=', $first_id)
                                        ->where('statements.id', '<=', $last_id)
                                        ->orderBy('statements.id');


                // There is no id to base off so we fall back to this query.
                if (!$first_id || !$last_id) {
                    Log::debug('There was no first or last id to base the day archives query from, so we fell back to the slow query');
                    $raw                = DB::table('statements')
                                            ->selectRaw($select_raw)
                                            ->where('statements.created_at', '>=', $date->format('Y-m-d') . ' 00:00:00')
                                            ->where('statements.created_at', '<=', $date->format('Y-m-d') . ' 23:59:59')
                                            ->orderBy('statements.id', 'desc');

                }

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
                });

                // Close off all the csv files, generate the zip, update the model, upload the files, complete the day archive.
                foreach ($day_archives as $day_archive) {
                    fclose($day_archive['csv_file']);
                    fclose($day_archive['csv_filelight']);

                    $zip = new ZipArchive;
                    if ($zip->open($day_archive['zippath'], ZipArchive::CREATE) === true) {
                        $zip->addFile($day_archive['path'], $day_archive['file']);
                        $zip->close();
                        $day_archive['model']->size = filesize($day_archive['zippath']);
                        $day_archive['model']->sha1 = sha1_file($day_archive['zippath']);
                        Storage::put($day_archive['zipfilesha1'], $day_archive['model']->sha1 . "  " . $day_archive['zipfile']);
                    } else {
                        throw new RuntimeException('Issue with creating the zip file.');
                    }
                    $day_archive['model']->save();

                    $ziplight = new ZipArchive;
                    if ($ziplight->open($day_archive['zippathlight'], ZipArchive::CREATE) === true) {
                        $ziplight->addFile($day_archive['pathlight'], $day_archive['filelight']);
                        $ziplight->close();
                        $day_archive['model']->sizelight = filesize($day_archive['zippathlight']);
                        $day_archive['model']->sha1light = sha1_file($day_archive['zippathlight']);
                        Storage::put($day_archive['zipfilelightsha1'], $day_archive['model']->sha1light . "  " . $day_archive['zipfilelight']);
                    } else {
                        throw new RuntimeException('Issue with creating the zip file.');
                    }
                    $day_archive['model']->save();

                    // Put them on the s3
                    Storage::disk('s3ds')->put($day_archive['zipfile'], fopen($day_archive['zippath'], 'rb'));
                    Storage::disk('s3ds')->put($day_archive['zipfilelight'], fopen($day_archive['zippathlight'], 'rb'));
                    Storage::disk('s3ds')->put($day_archive['zipfilesha1'], fopen($day_archive['zippathsha1'], 'rb'));
                    Storage::disk('s3ds')->put($day_archive['zipfilelightsha1'], fopen($day_archive['zippathlightsha1'], 'rb'));

                    // Clean up the files.
                    Storage::delete($day_archive['file']);
                    Storage::delete($day_archive['filelight']);
                    Storage::delete($day_archive['zipfile']);
                    Storage::delete($day_archive['zipfilelight']);
                    Storage::delete($day_archive['zipfilesha1']);
                    Storage::delete($day_archive['zipfilelightsha1']);

                    $day_archive['model']->completed_at = Carbon::now();
                    $day_archive['model']->save();
                }
            } else {
                throw new RuntimeException("Day archives have to be upload to a dedicated s3ds disk. please sure that there is one to write to.");
            }

            return true;
        }

        throw new RuntimeException("When creating a day export you must supply a date in the past.");
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

        $attempts_allowed = 500;

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

        $attempts_allowed = 500;

        $in = [];
        while($attempts_allowed-- > 0)
        {
            $in[] = $date->format('Y-m-d H:i:s');
            $date->subSecond();
        }
        return $in;
    }


    public function globalList()
    {
        return DayArchive::query()->whereNull('platform_id')->whereNotNull('completed_at')->orderBy('date', 'DESC');
    }

    public function platformList(Platform $platform)
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
        return DayArchive::query()->where('date', $date->format('Y-m-d'))->first();
    }

    /**
     * @param Carbon $date
     *
     * @return Builder
     */
    public function getDayArchivesByDate(Carbon $date): Builder
    {
        return DayArchive::query()->where('date', $date->format('Y-m-d'));
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