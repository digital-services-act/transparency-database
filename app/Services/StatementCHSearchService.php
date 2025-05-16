<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use ClickHouseDB\Client;
use App\Services\PlatformQueryService;

/**
 * @codeCoverageIgnore This whole service does many clickhouse calls. 
 */
class StatementCHSearchService
{
    // This service builds and does queries with clickhouse.

    private string $table = 'statements';

    private array $allowed_filters = [
        's',
        'decision_visibility',
        'decision_monetary',
        'decision_provision',
        'decision_account',
        'account_type',
        'decision_ground',
        'category',
        'content_type',
        'source_type',
        'content_language',
        'automated_detection',
        'automated_decision',
        'platform_id',
        'territorial_scope',
        'category_specification',
    ];

    private array $allowed_aggregate_attributes = [
        'automated_decision',
        'automated_detection',
        'category',
        'content_type_single',
        'decision_account',
        'decision_ground',
        'decision_monetary',
        'decision_provision',
        'decision_visibility_single',
        'platform_id',
        'source_type',
    ];

    // When caching, go for 25 hours. Just so that there is a overlap.
    public const ONE_DAY = 25 * 60 * 60;
    public const ONE_HOUR = 1 * 60 * 60;
    public const FIVE_MINUTES = 5 * 60;

    public function __construct(protected Client $client, protected PlatformQueryService $platformQueryService)
    {
    }

    public function startCountQuery(): string
    {
        return "SELECT COUNT(*) as count FROM {$this->table}";
    }

    public function buildWheres(array $conditions): string
    {
        if ($conditions !== []) {
            return " WHERE " . implode(" AND ", $conditions);
        }

        return '';
    }

    public function extractCountQueryResult(string $query): int
    {
        $result = $this->client->select($query);
        return (int) $result->rows()[0]['count'];
    }

    public function buildCountQuery(array $conditions = []): string
    {
        return $this->startCountQuery() . $this->buildWheres($conditions);
    }


    public function getCountQueryResult(array $conditions = []): int
    {
        return $this->extractCountQueryResult(
            $this->buildCountQuery($conditions)
        );
    }

    public function grandTotal(): int
    {
        return Cache::remember('grand_total', self::ONE_DAY, fn() => $this->grandTotalNoCache());
    }

    public function grandTotalNoCache(): int
    {
        return $this->getCountQueryResult();
    }

    public function receivedDateCondition(Carbon $date): string
    {
        return "toDate(created_at) = '" . $date->format('Y-m-d') . "'";
    }

    public function totalForDate(Carbon $date): int
    {
        return $this->getCountQueryResult([$this->receivedDateCondition($date)]);
    }

    public function totalForPlatformDate(Platform $platform, Carbon $date): int
    {
        return $this->getCountQueryResult([
            "platform_id = " . $platform->id,
            $this->receivedDateCondition($date),
        ]);
    }

    public function methodsByPlatformAll(): array
    {
        return Cache::remember('methods_by_platform_all', self::ONE_HOUR, function () {
            $dsa_team_platform_id = Platform::dsaTeamPlatformId();
            $query = "SELECT * FROM mv_methods_by_platform_all";
            $result = $this->client->select($query);
            $rows = $result->rows();
            return $rows;
        });
    }

    public function totalForPlatformIdAndMethod(int $platform_id, string $method): int
    {
        $totals = $this->methodsByPlatformAll();
        return $totals[$platform_id][$method] ?? 0;
    }

    public function receivedDateRangeCondition(Carbon $start, Carbon $end): string
    {
        return "toDate(created_at) BETWEEN '" . $start->format('Y-m-d') . "' AND '" . $end->format('Y-m-d') . "'";
    }

    public function totalForDateRange(Carbon $start, Carbon $end): int
    {
        return $this->getCountQueryResult([$this->receivedDateRangeCondition($start, $end)]);
    }

    /**
     * @return array
     */
    public function topCategories(): array
    {
        return Cache::remember('top_categories', self::ONE_DAY, fn() => $this->topCategoriesNoCache());
    }



    public function topCategoriesNoCache(): array
    {
        $query = "SELECT * FROM mv_categories_count ORDER BY count DESC";
        $result = $this->client->select($query);
        $rows = $result->rows();

        $acceptable_categories = array_keys(Statement::STATEMENT_CATEGORIES);

        $results = [];
        foreach ($rows as $row) {
            if (in_array($row['category'], $acceptable_categories)) {
                $results[] = [
                    'value' => $row['category'],
                    'total' => (int) $row['count'],
                ];
            }
        }

        return $results;
    }


    public function topDecisionVisibilities(): array
    {
        return Cache::remember('top_decisions_visibility', self::ONE_DAY, fn() => $this->topDecisionVisibilitiesNoCache());
    }

    public function topDecisionVisibilitiesNoCache(): array
    {
        $query = "SELECT * FROM mv_decision_visibility_counts WHERE visibility_value != '' AND visibility_value IS NOT NULL ORDER BY count DESC";
        $result = $this->client->select($query);
        $rows = $result->rows();

        $acceptable_visibilities = array_keys(Statement::DECISION_VISIBILITIES);

        $results = [];
        foreach ($rows as $row) {
            if (in_array($row['visibility_value'], $acceptable_visibilities)) {
                $results[] = [
                    'value' => $row['visibility_value'],
                    'total' => (int) $row['count'],
                ];
            }
        }

        return $results;
    }

    public function uuidToId(string $uuid): int
    {
        $query = "SELECT id FROM mv_uuid_to_id WHERE uuid = {uuid:String}";
        $result = $this->client->select($query, ['uuid' => $uuid]);
        $rows = $result->rows();
        if (count($rows) === 0) {
            return 0;
        }
        return (int) $rows[0]['id'];
    }

    /**
     * @return int
     */
    public function fullyAutomatedDecisionPercentage(): int
    {
        return Cache::remember('automated_decisions_percentage', self::ONE_DAY, fn() => $this->fullyAutomatedDecisionPercentageNoCache());
    }

    public function fullyAutomatedDecisionPercentageNoCache(): int
    {
        $query = "SELECT count FROM mv_automated_decisions_count WHERE automated_decision = 'AUTOMATED_DECISION_FULLY'";
        $result = $this->client->select($query);
        $rows = $result->rows();
        $automated_decision_count = (count($rows) > 0) ? (int) $rows[0]['count'] : 0;

        $total = $this->grandTotalNoCache();

        return round(($automated_decision_count / max(1, $total)) * 100);
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public function allSendingPlatformIds(): array
    {
        return Cache::remember('all_sending_platform_ids', self::ONE_HOUR, function () {
            $query = "select DISTINCT(platform_id) as platform_id from mv_methods_by_platform_all";
            $result = $this->client->select($query);
            $rows = $result->rows();
            return array_map(fn($row) => (int) $row['platform_id'], $rows);
        });
    }

    // Get all VLOP platform IDs
    private function vlopIds(): array
    {
        return Cache::remember('vlop_ids', self::ONE_HOUR, function () {
            $query = "SELECT id FROM platforms WHERE vlop = 1";
            $result = $this->client->select($query);
            $rows = $result->rows();
            if (count($rows) === 0) {
                return [];
            }
            return array_map(function ($row) {
                return (int) $row['id'];
            }, $rows);
        });
    }

    /**
     * Generate a SQL clause to exclude VLOP platforms
     * 
     * @return string SQL clause for excluding VLOP platforms
     */
    private function getNonVlopClause(): string
    {
        $vlop_ids = $this->vlopIds();

        return empty($vlop_ids)
            ? "platform_id != 0" // No VLOPs to filter, include all except platform 0 (if any)
            : "platform_id NOT IN (" . implode(',', $vlop_ids) . ")";
    }

    public function totalNonVlopPlatformsSending(): int
    {
        return Cache::remember('total_sending_non_vlop_platforms', self::ONE_HOUR, function () {
            $non_vlop_clause = $this->getNonVlopClause();

            $query = "SELECT COUNT(DISTINCT platform_id) AS count 
                     FROM mv_methods_by_platform_all 
                     WHERE $non_vlop_clause";

            $result = $this->client->select($query);
            return (int) $result->rows()[0]['count'];
        });
    }

    public function totalNonVlopPlatformsSendingApi(): int
    {
        return Cache::remember('total_sending_non_vlop_platforms_api', self::ONE_HOUR, function () {
            $non_vlop_clause = $this->getNonVlopClause();

            // Simple query with method constants directly in the query
            $query = "SELECT COUNT(DISTINCT platform_id) AS count 
                     FROM mv_methods_by_platform_all 
                     WHERE (method = '" . Statement::METHOD_API . "' OR method = '" . Statement::METHOD_API_MULTI . "') 
                     AND $non_vlop_clause";

            $result = $this->client->select($query);
            return (int) $result->rows()[0]['count'];
        });
    }

    public function totalNonVlopPlatformsSendingWebform(): int
    {
        return Cache::remember('total_sending_non_vlop_platforms_webform', self::ONE_HOUR, function () {
            $non_vlop_clause = $this->getNonVlopClause();

            // Simple query with method constant directly in the query
            $query = "SELECT COUNT(DISTINCT platform_id) AS count 
                     FROM mv_methods_by_platform_all 
                     WHERE method = '" . Statement::METHOD_FORM . "' 
                     AND $non_vlop_clause";

            $result = $this->client->select($query);
            return (int) $result->rows()[0]['count'];
        });
    }

    /**
     * Generate a SQL clause to include only VLOP platforms
     * 
     * @return string SQL clause for including only VLOP platforms
     */
    private function getVlopClause(): string
    {
        $vlop_ids = $this->vlopIds();

        return empty($vlop_ids)
            ? "platform_id = 0" // No VLOPs, ensure empty result set
            : "platform_id IN (" . implode(',', $vlop_ids) . ")";
    }

    public function totalVlopPlatformsSending(): int
    {
        return Cache::remember('total_sending_vlop_platforms', self::ONE_HOUR, function () {
            $vlop_clause = $this->getVlopClause();

            $query = "SELECT COUNT(DISTINCT platform_id) AS count 
                     FROM mv_methods_by_platform_all 
                     WHERE $vlop_clause";

            $result = $this->client->select($query);
            return (int) $result->rows()[0]['count'];
        });
    }

    public function totalVlopPlatformsSendingApi(): int
    {
        return Cache::remember('total_sending_vlop_platforms_api', self::ONE_HOUR, function () {
            $vlop_clause = $this->getVlopClause();

            // Simple query with method constants directly in the query
            $query = "SELECT COUNT(DISTINCT platform_id) AS count 
                     FROM mv_methods_by_platform_all 
                     WHERE (method = '" . Statement::METHOD_API . "' OR method = '" . Statement::METHOD_API_MULTI . "') 
                     AND $vlop_clause";

            $result = $this->client->select($query);
            return (int) $result->rows()[0]['count'];
        });
    }

    public function totalVlopPlatformsSendingWebform(): int
    {
        return Cache::remember('total_sending_vlop_platforms_webform', self::ONE_HOUR, function () {
            $vlop_clause = $this->getVlopClause();

            // Simple query with method constant directly in the query
            $query = "SELECT COUNT(DISTINCT platform_id) AS count 
                     FROM mv_methods_by_platform_all 
                     WHERE method = '" . Statement::METHOD_FORM . "' 
                     AND $vlop_clause";

            $result = $this->client->select($query);
            return (int) $result->rows()[0]['count'];
        });
    }

    public function platformNames(): array
    {
        return Cache::remember('platform_names', self::ONE_HOUR, function () {
            return $this->platformNamesNoCache();
        });
    }

    public function platformNamesNoCache(): array
    {
        $query = "SELECT id, name FROM platforms";
        $result = $this->client->select($query);
        $rows = $result->rows();
        return array_column($rows, 'name', 'id');
    }
    // Get the platform name for a given platform ID
    public function platformName(int $platform_id): string
    {
        $platform_names = $this->platformNames();
        return $platform_names[$platform_id] ?? 'Unknown';
    }

    public function aggregatesForDate(Carbon $date): array
    {
        $date_condition = $this->receivedDateCondition($date);

        // Build select clause with all allowed aggregate attributes
        $select_parts = ["toUInt64(COUNT(*)) as total"];
        $group_by_parts = [];

        foreach ($this->allowed_aggregate_attributes as $attribute) {
            if (str_ends_with($attribute, '_single')) {
                // Extract base field name by removing '_single' suffix
                $base_field = str_replace('_single', '', $attribute);
                $select_parts[] = "arrayStringConcat({$base_field}, '__') as {$attribute}";
            } else if ($attribute === 'automated_detection') {
                // Cast automated_detection to integer value instead of Yes/No string
                $select_parts[] = "toUInt8(automated_detection) as automated_detection";
            } else {
                $select_parts[] = $attribute;
            }
            $group_by_parts[] = $attribute;
        }

        $select_clause = implode(", ", $select_parts);
        $group_by_clause = implode(", ", $group_by_parts);

        $query = "SELECT {$select_clause}
            FROM {$this->table}
            WHERE {$date_condition}
            GROUP BY {$group_by_clause}";



        $result = $this->client->select($query);
        $rows = $result->rows();

        $total = 0;
        // Process the rows to ensure proper integer casting
        foreach ($rows as &$row) {
            $row['total'] = (int) $row['total'];

            // Ensure automated_detection is returned as integer
            if (isset($row['automated_detection'])) {
                $row['automated_detection'] = (int) $row['automated_detection'];
            }

            $row['platform_name'] = $this->platformNames()[$row['platform_id']] ?? 'Unknown';

            $permutation = [];
            foreach ($this->allowed_aggregate_attributes as $attribute) {
                if (isset($row[$attribute])) {
                    $permutation[] = "{$attribute}:{$row[$attribute]}";
                }
            }
            $row['permutation'] = implode(',', $permutation);

            $total += $row['total'];
        }

        $out = [];
        $out['total'] = $total;
        $out['aggregates'] = $rows;
        $out['date'] = $date->format('Y-m-d');
        $out['total_aggregates'] = count($rows);

        return $out;
    }

    public function aggregatesForDateFromView(Carbon $date): array
    {
        $query = "SELECT * FROM mv_daily_statement_aggregates WHERE date = '{$date->format('Y-m-d')}'";

        $result = $this->client->select($query);
        $rows = $result->rows();

        $total = 0;
        // Process the rows to ensure proper integer casting
        foreach ($rows as &$row) {
            $row['total'] = (int) $row['total'];

            // Ensure automated_detection is returned as integer
            if (isset($row['automated_detection'])) {
                $row['automated_detection'] = (int) $row['automated_detection'];
            }

            $row['platform_name'] = $this->platformNames()[$row['platform_id']] ?? 'Unknown';

            $permutation = [];
            foreach ($this->allowed_aggregate_attributes as $attribute) {
                if (isset($row[$attribute])) {
                    $permutation[] = "{$attribute}:{$row[$attribute]}";
                }
            }
            $row['permutation'] = implode(',', $permutation);
            unset($row['date']);

            $total += $row['total'];
        }

        // construct the output, including the date
        $out = [];
        $out['total'] = $total;
        $out['total_aggregates'] = count($rows);
        $out['date'] = $date->format('Y-m-d');
        $out['aggregates'] = $rows;

        return $out;
    }
}

