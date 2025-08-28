<?php

namespace App\Services;

use App\Exports\StatementExportTrait;
use App\Models\DayArchive;
use App\Models\Platform;
use App\Models\PlatformPuid;
use App\Models\Statement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DayArchiveService
{
    use StatementExportTrait;

    public function __construct(protected PlatformQueryService $platform_query_service) {}

    public function buildBasicExportsArray(): array
    {
        $exports = [];

        $global = [
            'slug' => 'global',
            'id' => null,
        ];
        $exports[] = $global;

        $platforms = $this->platform_query_service->getPlatformsById();

        foreach ($platforms as $id => $name) {
            $export = [
                'slug' => Str::slug($name),
                'id' => $id,
            ];
            $exports[$id] = $export;
        }

        return $exports;
    }

    public function getFirstIdOfDate(Carbon $date)
    {
        // Start at the beginning of the specified minute
        $startOfMinute = $date->copy()->setTime(0, 0, 0);
        $firstId = false;

        for ($i = 0; $i < 60; $i++) {
            // For each iteration, add seconds to the starting point
            $currentSecond = $startOfMinute->copy()->addSeconds($i);

            // Query the database for the minimum ID created exactly at this second
            $first = Statement::query()
                ->selectRaw('min(id) as min')
                ->where('created_at', $currentSecond->format('Y-m-d H:i:s'))
                ->first();

            // If a result is found, return the id
            if ($first && $first->min) {
                return $first->min;
            }
        }

        // Return false if no ID is found within the minute
        return $firstId;
    }

    public function getFirstPlatformPuidIdOfDate(Carbon $date)
    {
        // Start at the beginning of the specified minute
        $startOfMinute = $date->copy()->setTime(0, 0, 0);
        $firstId = false;

        for ($i = 0; $i < 60; $i++) {
            // For each iteration, add seconds to the starting point
            $currentSecond = $startOfMinute->copy()->addSeconds($i);

            // Query the database for the minimum ID created exactly at this second
            $first = PlatformPuid::query()
                ->selectRaw('min(id) as min')
                ->where('created_at', $currentSecond->format('Y-m-d H:i:s'))
                ->first();

            // If a result is found, return the id
            if ($first && $first->min) {
                return $first->min;
            }
        }

        // Return false if no ID is found within the minute
        return $firstId;
    }

    public function getLastIdOfDate(Carbon $date)
    {
        // Set the time to 23:59:59 of the given date
        $endOfDay = $date->copy()->setTime(23, 59, 59); // '2030-01-01 23:59:59'
        $lastId = false;

        // Loop through the last minute, going backwards from 23:59:59 to 23:59:00
        for ($i = 0; $i < 60; $i++) {
            // Subtract seconds to move backwards
            $currentSecond = $endOfDay->copy()->subSeconds($i);

            // Query the database for the maximum ID created exactly at this second
            $last = Statement::query()
                ->selectRaw('max(id) as max')
                ->where('created_at', $currentSecond->format('Y-m-d H:i:s'))
                ->first();

            // If a result is found, return the id immediately
            if ($last && $last->max) {
                return $last->max;
            }
        }

        // Return false if no ID is found within the last minute
        return $lastId;
    }

    public function getLastPlatformPuidIdOfDate(Carbon $date)
    {
        // Set the time to 23:59:59 of the given date
        $endOfDay = $date->copy()->setTime(23, 59, 59); // '2030-01-01 23:59:59'
        $lastId = false;

        // Loop through the last minute, going backwards from 23:59:59 to 23:59:00
        for ($i = 0; $i < 60; $i++) {
            // Subtract seconds to move backwards
            $currentSecond = $endOfDay->copy()->subSeconds($i);

            // Query the database for the maximum ID created exactly at this second
            $last = PlatformPuid::query()
                ->selectRaw('max(id) as max')
                ->where('created_at', $currentSecond->format('Y-m-d H:i:s'))
                ->first();

            // If a result is found, return the id immediately
            if ($last && $last->max) {
                return $last->max;
            }
        }

        // Return false if no ID is found within the last minute
        return $lastId;
    }

    public function globalList(): Builder
    {
        return DayArchive::query()->whereNull('platform_id')->whereNotNull('completed_at')->orderBy('date', 'DESC');
    }

    public function platformList(Platform $platform): Builder
    {
        return DayArchive::query()->where('platform_id', $platform->id)->whereNotNull('completed_at')->orderBy(
            'date',
            'DESC'
        );
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

    private function cleanTextField(string $fieldName): string
    {
        return "REPLACE(REPLACE({$fieldName}, '\n', ' '), '\\\\\"', '\"') AS {$fieldName}";
    }

    public function getSelectRawString(): string
    {
        $selects = [];
        $selects[] = 'id';
        $selects[] = 'uuid';
        $selects[] = 'decision_visibility';
        $selects[] = $this->cleanTextField('decision_visibility_other');
        $selects[] = 'end_date_visibility_restriction';

        $selects[] = 'decision_monetary';
        $selects[] = $this->cleanTextField('decision_monetary_other');
        $selects[] = 'end_date_monetary_restriction';

        $selects[] = 'decision_provision';
        $selects[] = 'end_date_service_restriction';

        $selects[] = 'decision_account';
        $selects[] = 'end_date_account_restriction';
        $selects[] = 'account_type';

        $selects[] = 'decision_ground';
        $selects[] = $this->cleanTextField('decision_ground_reference_url');

        $selects[] = $this->cleanTextField('illegal_content_legal_ground');
        $selects[] = $this->cleanTextField('illegal_content_explanation');
        $selects[] = $this->cleanTextField('incompatible_content_ground');
        $selects[] = $this->cleanTextField('incompatible_content_explanation');
        $selects[] = 'incompatible_content_illegal';

        $selects[] = 'category';
        $selects[] = 'category_addition';
        $selects[] = 'category_specification';
        $selects[] = $this->cleanTextField('category_specification_other');

        $selects[] = 'content_type';
        $selects[] = $this->cleanTextField('content_type_other');
        $selects[] = 'content_language';
        $selects[] = 'content_date';
        $selects[] = 'content_id_ean';

        $selects[] = 'territorial_scope';
        $selects[] = 'application_date';
        $selects[] = $this->cleanTextField('decision_facts');

        $selects[] = 'source_type';
        $selects[] = $this->cleanTextField('source_identity');

        $selects[] = 'automated_detection';
        $selects[] = 'automated_decision';

        $selects[] = $this->cleanTextField('puid');
        $selects[] = 'created_at';
        $selects[] = 'platform_id';

        return implode(', ', $selects);
    }
}
