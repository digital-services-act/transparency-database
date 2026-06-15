<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @codeCoverageIgnore This service does Elasticsearch SQL and aggregation calls.
 */
class StatementElasticStatsService
{
    // When caching, go for 25 hours. Just so that there is an overlap.
    public const ONE_DAY = 25 * 60 * 60;

    public const ONE_HOUR = 1 * 60 * 60;

    public function __construct(
        protected PlatformQueryService $platformQueryService,
        private readonly StatementElasticConnectionService $connectionService,
        private readonly StatementElasticAggregationService $aggregationService,
    ) {}

    private function client(): Client
    {
        return $this->connectionService->client();
    }

    private function indexName(): string
    {
        return $this->connectionService->statementIndexName();
    }

    public function allSendingPlatformIds(): array
    {
        return Cache::remember('all_sending_platform_ids', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_platform_ids = [];
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                foreach ($methods as $method => $total) {
                    if ($total) {
                        $sending_platform_ids[] = $platform_id;
                        break;
                    }
                }
            }

            return $sending_platform_ids;
        });
    }

    public function totalNonVlopPlatformsSending(): int
    {
        return Cache::remember('total_sending_non_vlop_platforms', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_non_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if (! in_array($platform_id, $vlop_ids, true)) {
                    foreach ($methods as $method => $total) {
                        if ($total) {
                            $sending_non_vlop_platform_ids[] = $platform_id;
                            break;
                        }
                    }
                }
            }

            return count($sending_non_vlop_platform_ids);
        });
    }

    public function totalNonVlopPlatformsSendingApi(): int
    {
        return Cache::remember('total_sending_non_vlop_platforms_api', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_api_non_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if (($methods[Statement::METHOD_API] || $methods[Statement::METHOD_API_MULTI]) && ! in_array($platform_id, $vlop_ids, true)) {
                    $sending_api_non_vlop_platform_ids[] = $platform_id;
                }
            }

            return count($sending_api_non_vlop_platform_ids);
        });
    }

    public function totalNonVlopPlatformsSendingWebform(): int
    {
        return Cache::remember('total_sending_non_vlop_platforms_webform', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_webform_non_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if ($methods[Statement::METHOD_FORM] && ! in_array($platform_id, $vlop_ids, true)) {
                    $sending_webform_non_vlop_platform_ids[] = $platform_id;
                }
            }

            return count($sending_webform_non_vlop_platform_ids);
        });
    }

    public function totalVlopPlatformsSending(): int
    {
        return Cache::remember('total_sending_vlop_platforms', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if (in_array($platform_id, $vlop_ids, true)) {
                    foreach ($methods as $method => $total) {
                        if ($total) {
                            $sending_vlop_platform_ids[] = $platform_id;
                            break;
                        }
                    }
                }
            }

            return count($sending_vlop_platform_ids);
        });
    }

    public function totalVlopPlatformsSendingApi(): int
    {
        return Cache::remember('total_sending_vlop_platforms_api', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_api_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if (($methods[Statement::METHOD_API] || $methods[Statement::METHOD_API_MULTI]) && in_array($platform_id, $vlop_ids, true)) {
                    $sending_api_vlop_platform_ids[] = $platform_id;
                }
            }

            return count($sending_api_vlop_platform_ids);
        });
    }

    public function totalVlopPlatformsSendingWebform(): int
    {
        return Cache::remember('total_sending_vlop_platforms_webform', self::ONE_HOUR, function () {
            $platform_ids_methods_data = $this->methodsByPlatformAll();
            $sending_webform_vlop_platform_ids = [];
            $vlop_ids = $this->vlopIds();
            foreach ($platform_ids_methods_data as $platform_id => $methods) {
                if ($methods[Statement::METHOD_FORM] && in_array($platform_id, $vlop_ids, true)) {
                    $sending_webform_vlop_platform_ids[] = $platform_id;
                }
            }

            return count($sending_webform_vlop_platform_ids);
        });
    }

    private function vlopIds(): array
    {
        return $this->platformQueryService->getVlopPlatformIds();
    }

    public function startCountQuery(): string
    {
        return 'SELECT CAST(count(*) AS BIGINT) as count FROM '.$this->indexName();
    }

    public function buildWheres(array $conditions): string
    {
        if ($conditions !== []) {
            return ' WHERE '.implode(' AND ', $conditions);
        }

        return '';
    }

    public function extractCountQueryResult($result): int
    {
        return (int) ($result['rows'][0][0] ?? 0);
    }

    public function runSql(string $sql): array
    {
        return $this->client()->sql()->query([
            'body' => [
                'query' => $sql,
            ],
            'format' => 'json',
        ])->asArray();
    }

    public function runAndExtractCountQuerySql(string $sql): int
    {
        return $this->extractCountQueryResult($this->runSql($sql));
    }

    public function getCountQueryResult(array $conditions = []): int
    {
        return $this->extractCountQueryResult($this->runSql($this->startCountQuery().$this->buildWheres($conditions)));
    }

    public function highestId(): int
    {
        $sql = 'SELECT max(id) AS max_id FROM '.$this->indexName();
        $result = $this->runSql($sql);

        return (int) ($result['rows'][0][0] ?? 0);
    }

    public function grandTotal(): int
    {
        return Cache::remember('grand_total', self::ONE_DAY, fn () => $this->grandTotalNoCache());
    }

    public function grandTotalNoCache(): int
    {
        return $this->getCountQueryResult();
    }

    public function receivedDateCondition(Carbon $date): string
    {
        return "received_date = '".$date->format('Y-m-d')."'";
    }

    public function totalForDate(Carbon $date): int
    {
        return $this->getCountQueryResult([$this->receivedDateCondition($date)]);
    }

    public function totalForPlatformDate(Platform $platform, Carbon $date): int
    {
        return $this->getCountQueryResult([
            'platform_id = '.$platform->id,
            $this->receivedDateCondition($date),
        ]);
    }

    public function totalsForPlatformsDate(Carbon $date): array
    {
        $aggregates = $this->aggregationService->processDateAggregate($date, ['platform_id']);

        return $aggregates['aggregates'];
    }

    public function methodsByPlatformsDate(Carbon $date): array
    {
        $query = 'SELECT COUNT(*), method, platform_id FROM '.$this->indexName()." WHERE received_date = '".$date->format('Y-m-d')."' GROUP BY platform_id, method";

        return $this->extractMethodAggregateFromQuery($query);
    }

    public function methodsByPlatformAll(): array
    {
        return Cache::remember('methods_by_platform_all', self::ONE_HOUR, function () {
            $dsa_team_platform_id = Platform::dsaTeamPlatformId();
            $query = 'SELECT CAST(count(*) AS BIGINT), method, platform_id FROM '.$this->indexName().' WHERE platform_id <> '.$dsa_team_platform_id.' GROUP BY platform_id, method';

            return $this->extractMethodAggregateFromQuery($query);
        });

    }

    private function extractMethodAggregateFromQuery(string $query): array
    {

        $out = [];
        if ($this->connectionService->isConfigured()) {
            $results = $this->runSql($query);
            $rows = $results['rows'];
            foreach ($rows as [$total, $method, $platform_id]) {
                $out[$platform_id][$method] = $total;
            }
        }

        foreach ($out as $platform_id => $methods) {
            $out[$platform_id][Statement::METHOD_FORM] ??= 0;
            $out[$platform_id][Statement::METHOD_API] ??= 0;
            $out[$platform_id][Statement::METHOD_API_MULTI] ??= 0;
        }

        return $out;
    }

    public function totalForPlatformIdAndMethod(int $platform_id, string $method): int
    {
        $totals = $this->methodsByPlatformAll();

        return $totals[$platform_id][$method] ?? 0;
    }

    public function receivedDateRangeCondition(Carbon $start, Carbon $end): string
    {
        return "received_date BETWEEN '".$start->format('Y-m-d')."' AND '".$end->format('Y-m-d')."'";
    }

    public function totalForDateRange(Carbon $start, Carbon $end): int
    {
        return $this->getCountQueryResult([$this->receivedDateRangeCondition($start, $end)]);
    }

    public function datesTotalsForRange(Carbon $start, Carbon $end): array
    {
        $prepare = [];
        $current = $start->clone();
        while ($current <= $end) {
            $prepare[$current->format('Y-m-d')] = 0;
            $current->addDay();
        }

        $results = $this->aggregationService->processRangeAggregate($start, $end, ['received_date']);

        foreach ($results['aggregates'] as $aggregate) {
            $prepare[$aggregate['received_date']] = $aggregate['total'];
        }

        return array_map(static fn ($date, $total) => [
            'date' => $date,
            'total' => $total,
        ], array_keys($prepare), array_values($prepare));
    }

    public function topCategories(): array
    {
        return Cache::remember('top_categories', self::ONE_DAY, fn () => $this->topCategoriesNoCache());
    }

    public function topCategoriesNoCache(): array
    {
        $results = [];
        $categories = array_keys(Statement::STATEMENT_CATEGORIES);
        foreach ($categories as $category) {
            $results[] = [
                'value' => $category,
                'total' => $this->getCountQueryResult(["category = '".$category."'"]),
            ];
        }

        uasort($results, static fn ($a, $b) => ($a['total'] <=> $b['total']) * -1);

        return $results;
    }

    public function topDecisionVisibilities(): array
    {
        return Cache::remember('top_decisions_visibility', self::ONE_DAY, fn () => $this->topDecisionVisibilitiesNoCache());
    }

    public function topDecisionVisibilitiesNoCache(): array
    {
        $results = [];
        $decision_visibilities = array_keys(Statement::DECISION_VISIBILITIES);
        foreach ($decision_visibilities as $decision_visibility) {
            $results[] = [
                'value' => $decision_visibility,
                'total' => $this->getCountQueryResult(["decision_visibility_single = '".$decision_visibility."'"]),
            ];
        }

        uasort($results, static fn ($a, $b) => ($a['total'] <=> $b['total']) * -1);

        return $results;
    }

    public function fullyAutomatedDecisionPercentage(): int
    {
        return Cache::remember('automated_decisions_percentage', self::ONE_DAY, fn () => $this->fullyAutomatedDecisionPercentageNoCache());
    }

    public function fullyAutomatedDecisionPercentageNoCache(): int
    {
        $automated_decision_count = $this->getCountQueryResult(["automated_decision = 'AUTOMATED_DECISION_FULLY'"]);
        $total = $this->grandTotal();

        return round((($automated_decision_count / max(1, $total)) * 100));
    }
}
