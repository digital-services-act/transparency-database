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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DayArchiveService
{
    use StatementExportTrait;

    private const int DEFAULT_BOUNDARY_WINDOW_MINUTES = 2;

    // Table to use for fetching statements
    public $statements_table = 'statements_beta';

    // Database connection to use (defaults to pgsql, can be overridden for testing)
    public $connection = 'pgsql';

    // Define the versions of CSV exports
    public $versions = [
        'full',
        'light',
    ];

    // Number of statements to process in each subpart
    public $chunk = 100000;

    public $platforms = [];

    public function __construct(protected PlatformQueryService $platform_query_service)
    {
        $this->platforms = $platform_query_service->getPlatformsById();
    }

    /**
     * Build the basic exports array structure.
     *
     * this will include the 'global' export as well as one for each platform
     *
     * the structure is:
     * [
     *   ['id' => null, 'slug' => 'global'],
     *   ['id' => 1, 'slug' => 'platform-name'],
     *   ...
     * ]
     *
     * @return array<array|array{id: null, slug: string}>
     */
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

    public function getFirstIdOfDate(Carbon $date, int $boundaryMinutes = self::DEFAULT_BOUNDARY_WINDOW_MINUTES)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $startOfDay->copy()->addDay();

        $query = Statement::query()
            ->where('created_at', '>=', $startOfDay)
            ->where('created_at', '<', $endOfDay);

        return $this->getFirstIdFromBoundaryWindow($query, $startOfDay, $endOfDay, $boundaryMinutes);
    }

    public function getFirstPlatformPuidIdOfDate(Carbon $date, int $boundaryMinutes = self::DEFAULT_BOUNDARY_WINDOW_MINUTES)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $startOfDay->copy()->addDay();

        $query = PlatformPuid::query()
            ->where('created_at', '>=', $startOfDay)
            ->where('created_at', '<', $endOfDay);

        return $this->getFirstIdFromBoundaryWindow($query, $startOfDay, $endOfDay, $boundaryMinutes);
    }

    public function getLastIdOfDate(Carbon $date, int $boundaryMinutes = self::DEFAULT_BOUNDARY_WINDOW_MINUTES)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $startOfDay->copy()->addDay();

        $query = Statement::query()
            ->where('created_at', '>=', $startOfDay)
            ->where('created_at', '<', $endOfDay);

        return $this->getLastIdFromBoundaryWindow($query, $startOfDay, $endOfDay, $boundaryMinutes);
    }

    public function getLastPlatformPuidIdOfDate(Carbon $date, int $boundaryMinutes = self::DEFAULT_BOUNDARY_WINDOW_MINUTES)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $startOfDay->copy()->addDay();

        $query = PlatformPuid::query()
            ->where('created_at', '>=', $startOfDay)
            ->where('created_at', '<', $endOfDay);

        return $this->getLastIdFromBoundaryWindow($query, $startOfDay, $endOfDay, $boundaryMinutes);
    }

    private function getFirstIdFromBoundaryWindow(Builder $query, Carbon $startOfDay, Carbon $endOfDay, int $boundaryMinutes)
    {
        if ($boundaryMinutes > 0) {
            $boundaryEnd = $startOfDay->copy()->addMinutes($boundaryMinutes)->min($endOfDay);
            $id = (clone $query)
                ->where('created_at', '<', $boundaryEnd)
                ->min('id');

            if ($id) {
                return $id;
            }
        }

        return (clone $query)
            ->orderBy('created_at')
            ->orderBy('id')
            ->value('id') ?: false;
    }

    private function getLastIdFromBoundaryWindow(Builder $query, Carbon $startOfDay, Carbon $endOfDay, int $boundaryMinutes)
    {
        if ($boundaryMinutes > 0) {
            $boundaryStart = $endOfDay->copy()->subMinutes($boundaryMinutes)->max($startOfDay);
            $id = (clone $query)
                ->where('created_at', '>=', $boundaryStart)
                ->max('id');

            if ($id) {
                return $id;
            }
        }

        return (clone $query)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('id') ?: false;
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

    public function getSelectRawString(string $table = 'statements_beta'): string
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

    public function prepareHeadingsArray(): array
    {
        $headings = [];
        $headings['full'] = $this->headings();
        $headings['light'] = $this->headingsLight();

        return $headings;
    }

    /**
     * Map the statement to rows for each version.
     *
     * @param  mixed  $statement  The raw statement object from database query.
     * @return array<string, array<int, string|null>> An associative array where keys are version names
     *                                                ('full', 'light', etc.) and values are arrays representing the mapped rows for each version.
     */
    public function mapRows(mixed $statement): array
    {
        $rows = [];

        foreach ($this->versions as $version) {
            $function = 'mapRaw'.Str::ucfirst($version);
            $row = $this->$function($statement, $this->platforms);
            $rows[$version] = $row;
        }

        return $rows;
    }

    public function csvstr(array $fields): string
    {
        $f = fopen('php://memory', 'wb+');
        fputcsv($f, $fields);

        rewind($f);
        $csv_line = stream_get_contents($f);

        return rtrim($csv_line);
    }

    /**
     * Build CSV lines for each version from the given statement.
     *
     * @param  mixed  $statement  The raw statement object from database query to convert to CSV lines.
     * @return array<string, string> An associative array where keys are version names ('full', 'light', etc.)
     *                               and values are the corresponding CSV lines as strings.
     */
    public function buildCsvLines(mixed $statement): array
    {
        $csvs = [];
        $rows = $this->mapRows($statement);
        foreach ($this->versions as $version) {
            $csvs[$version] = $this->csvstr($rows[$version]);
        }

        return $csvs;
    }

    public function getRawStatements(int $start, int $end, string $date = ''): Collection
    {
        $select_raw = $this->getSelectRawString();
        $statements = DB::connection($this->connection)
            ->table($this->statements_table)
            ->selectRaw($select_raw)
            ->where('id', '>=', $start)
            ->where('id', '<=', $end)
            ->when($date, function ($query, $date) {

                $startOfDay = $date.' 00:00:00';
                $endOfDay = $date.' 23:59:59';

                $query->where('created_at', '>=', $startOfDay)
                    ->where('created_at', '<=', $endOfDay);

                return $query;

            })
            ->orderBy('id')
            ->get();

        return $statements;
    }
}
