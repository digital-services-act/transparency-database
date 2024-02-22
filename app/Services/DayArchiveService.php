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

    public function __construct(protected StatementSearchService $statement_search_service)
    {
    }

    public function buildBasicExportsArray(): array
    {
        $exports = [];

        $global    = [
            'slug' => 'global',
            'id'   => null,
        ];
        $exports[] = $global;

        $platforms = Platform::NonDsa()->get();

        foreach ($platforms as $platform) {
            $export             = [
                'slug' => $platform->slugifyName(),
                'id'   => $platform->id
            ];
            $exports[$platform->id] = $export;
        }

        return $exports;
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

        $attempts_allowed = 10;

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

        $attempts_allowed = 10;

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

    public function getDayArchiveByDate(Carbon $date): DayArchive|Model|Builder|null
    {
        return DayArchive::query()->whereDate('date', $date)->whereNull('platform_id')->first();
    }

    public function getDayArchiveByPlatformDate(Platform $platform, Carbon $date): DayArchive|Model|Builder|null
    {
        return DayArchive::query()->whereDate('date', $date)->where('platform_id', $platform->id)->first();
    }

    public function getDayArchivesByDate(Carbon $date): Builder
    {
        return DayArchive::query()->whereDate('date', $date);
    }

    public function getSelectRawString(): string
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