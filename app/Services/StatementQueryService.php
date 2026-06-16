<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * This service does raw queries and such on the statements table.
 * This should only be used for local development purposes.
 * Even then you should setup a local dev opensearch account and use the searching service with indexing.
 * Look in the .env.example
 * #SCOUT_DRIVER=opensearch
 * #OPENSEARCH_HOST=XXX
 * #OPENSEARCH_USERNAME=XXXX
 * #OPENSEARCH_PASSWORD=XXXXS
 */
class StatementQueryService
{
    // These are the filters that we are allowed to filter on.
    // If there is to be a new filter, then add it here first and then make
    // a function. new_attribute -> applyNewAttributeFilter()

    // This service builds and does queries with the database.

    private array $allowed_filters = [
        's',
        'platform_id',
        'automated_detection',
        'automated_decision',
        'created_at_start',
        'created_at_end',
        'decision_ground',
        'decision_visibility',
        'decision_monetary',
        'decision_provision',
        'decision_account',
        'account_type',
        'category',
        'category_specification',
        'content_type',
        'content_language',
        'territorial_scope',
        'source_type',
    ];

    public function query(array $filters): Builder
    {
        $statements = Statement::query();
        foreach ($this->allowed_filters as $filter_key) {
            if (isset($filters[$filter_key]) && $filters[$filter_key]) {
                $method = sprintf('apply%sFilter', ucfirst(Str::camel($filter_key)));
                try {
                    if (method_exists($this, $method)) {
                        $this->$method($statements, $filters[$filter_key]);
                    }
                } catch (\TypeError|\Exception $e) {
                    Log::error('Statement Query Service Error', ['exception' => $e]);
                }
            }
        }

        return $statements;
    }

    public function grandTotal(): int
    {
        return Statement::query()->count();
    }

    /**
     * @return array<int, array{value: string, total: int}>
     */
    public function topCategories(): array
    {
        return $this->rankedColumnCounts(array_keys(Statement::STATEMENT_CATEGORIES), 'category');
    }

    /**
     * @return array<int, array{value: string, total: int}>
     */
    public function topDecisionVisibilities(): array
    {
        $results = [];

        foreach (array_keys(Statement::DECISION_VISIBILITIES) as $decision_visibility) {
            $results[] = [
                'value' => $decision_visibility,
                'total' => $this->query(['decision_visibility' => [$decision_visibility]])->count(),
            ];
        }

        return $this->sortRankedCounts($results);
    }

    public function fullyAutomatedDecisionPercentage(): int
    {
        $automated_decision_count = Statement::query()
            ->where('automated_decision', 'AUTOMATED_DECISION_FULLY')
            ->count();

        return (int) round((($automated_decision_count / max(1, $this->grandTotal())) * 100));
    }

    public function allSendingPlatformIds(): array
    {
        $sending_platform_ids = [];

        foreach ($this->methodsByPlatformAll() as $platform_id => $methods) {
            if ($this->hasAnyMethodTotal($methods)) {
                $sending_platform_ids[] = (int) $platform_id;
            }
        }

        return $sending_platform_ids;
    }

    public function totalNonVlopPlatformsSending(): int
    {
        return $this->countSendingPlatforms(false);
    }

    public function totalNonVlopPlatformsSendingApi(): int
    {
        return $this->countSendingPlatforms(false, [
            Statement::METHOD_API,
            Statement::METHOD_API_MULTI,
        ]);
    }

    public function totalNonVlopPlatformsSendingWebform(): int
    {
        return $this->countSendingPlatforms(false, [Statement::METHOD_FORM]);
    }

    public function totalVlopPlatformsSending(): int
    {
        return $this->countSendingPlatforms(true);
    }

    public function totalVlopPlatformsSendingApi(): int
    {
        return $this->countSendingPlatforms(true, [
            Statement::METHOD_API,
            Statement::METHOD_API_MULTI,
        ]);
    }

    public function totalVlopPlatformsSendingWebform(): int
    {
        return $this->countSendingPlatforms(true, [Statement::METHOD_FORM]);
    }

    public function methodsByPlatformAll(): array
    {
        $rows = Statement::query()
            ->selectRaw('COUNT(*) as total, method, platform_id')
            ->where('platform_id', '<>', Platform::dsaTeamPlatformId())
            ->groupBy('platform_id', 'method')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $platform_id = (int) $row->platform_id;
            $out[$platform_id][$row->method] = (int) $row->total;
        }

        foreach ($out as $platform_id => $methods) {
            $out[$platform_id][Statement::METHOD_FORM] ??= 0;
            $out[$platform_id][Statement::METHOD_API] ??= 0;
            $out[$platform_id][Statement::METHOD_API_MULTI] ??= 0;
        }

        return $out;
    }

    /**
     * @param  array<int, string>  $values
     * @return array<int, array{value: string, total: int}>
     */
    private function rankedColumnCounts(array $values, string $column): array
    {
        $counts = Statement::query()
            ->select($column)
            ->selectRaw('COUNT(*) as total')
            ->whereIn($column, $values)
            ->groupBy($column)
            ->pluck('total', $column)
            ->toArray();

        $results = array_map(static fn (string $value): array => [
            'value' => $value,
            'total' => (int) ($counts[$value] ?? 0),
        ], $values);

        return $this->sortRankedCounts($results);
    }

    /**
     * @param  array<int, array{value: string, total: int}>  $results
     * @return array<int, array{value: string, total: int}>
     */
    private function sortRankedCounts(array $results): array
    {
        usort($results, static fn (array $a, array $b): int => $b['total'] <=> $a['total']);

        return $results;
    }

    /**
     * @param  array<int, string>|null  $method_filter
     */
    private function countSendingPlatforms(bool $vlop, ?array $method_filter = null): int
    {
        $count = 0;
        $vlop_ids = $this->vlopIds();

        foreach ($this->methodsByPlatformAll() as $platform_id => $methods) {
            $platform_is_vlop = in_array((int) $platform_id, $vlop_ids, true);
            if ($platform_is_vlop !== $vlop) {
                continue;
            }

            $method_totals = $method_filter === null
                ? $methods
                : array_intersect_key($methods, array_flip($method_filter));

            if ($this->hasAnyMethodTotal($method_totals)) {
                $count++;
            }
        }

        return $count;
    }

    private function hasAnyMethodTotal(array $methods): bool
    {
        foreach ($methods as $total) {
            if ((int) $total > 0) {
                return true;
            }
        }

        return false;
    }

    private function vlopIds(): array
    {
        return Platform::Vlops()
            ->pluck('id')
            ->map(static fn (int $id): int => $id)
            ->toArray();
    }

    private function applySFilter(Builder $query, string $filter_value): void
    {
        $query->where(function ($q) use ($filter_value) {
            $q->orWhereLike('incompatible_content_ground', '%'.$filter_value.'%')
                ->orWhereLike('incompatible_content_explanation', '%'.$filter_value.'%')
                ->orWhereLike('illegal_content_legal_ground', '%'.$filter_value.'%')
                ->orWhereLike('illegal_content_explanation', '%'.$filter_value.'%')
                ->orWhereLike('decision_facts', '%'.$filter_value.'%')
                ->orWhereLike('uuid', '%'.$filter_value.'%')
                ->orWhereLike('puid', '%'.$filter_value.'%')
                ->orWhereLike('decision_visibility_other', '%'.$filter_value.'%')
                ->orWhereLike('decision_monetary_other', '%'.$filter_value.'%')
                ->orWhereLike('content_type_other', '%'.$filter_value.'%')
                ->orWhereLike('source_identity', '%'.$filter_value.'%')
                ->orWhereLike('content_id_ean', '%'.$filter_value.'%');
        });
    }

    private function applyPlatformIdFilter(Builder $query, array $filter_value): void
    {
        $query->whereHas('platform', static function ($inner_query) use ($filter_value) {
            $inner_query->whereIn('platforms.id', $filter_value);
        });
    }

    private function applySourceTypeFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::SOURCE_TYPES));
        if ($filter_values_validated !== []) {
            $query->whereIn('source_type', $filter_values_validated);
        }
    }

    private function applyAutomatedDetectionFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, Statement::AUTOMATED_DETECTIONS);
        if ($filter_values_validated !== []) {
            $query->whereIn('automated_detection', $filter_values_validated);
        }
    }

    private function applyAutomatedDecisionFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::AUTOMATED_DECISIONS));
        if ($filter_values_validated !== []) {
            $query->whereIn('automated_decision', $filter_values_validated);
        }
    }

    private function applyDecisionGroundFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_GROUNDS));
        if ($filter_values_validated !== []) {
            $query->whereIn('decision_ground', $filter_values_validated);
        }
    }

    private function applyDecisionVisibilityFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_VISIBILITIES));
        if ($filter_values_validated !== []) {
            $query->where(function ($query) use ($filter_values_validated) {
                foreach ($filter_values_validated as $value) {
                    $query->orWhereJsonContains('decision_visibility', $value);
                }
            });
        }
    }

    private function applyDecisionMonetaryFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_MONETARIES));
        if ($filter_values_validated !== []) {
            $query->whereIn('decision_monetary', $filter_values_validated);
        }
    }

    private function applyDecisionProvisionFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_PROVISIONS));
        if ($filter_values_validated !== []) {
            $query->whereIn('decision_provision', $filter_values_validated);
        }
    }

    private function applyDecisionAccountFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_ACCOUNTS));
        if ($filter_values_validated !== []) {
            $query->whereIn('decision_account', $filter_values_validated);
        }
    }

    private function applyAccountTypeFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::ACCOUNT_TYPES));
        if ($filter_values_validated !== []) {
            $query->whereIn('account_type', $filter_values_validated);
        }
    }

    private function applyCategorySpecificationFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::KEYWORDS));
        if ($filter_values_validated !== []) {
            $query->where(function ($query) use ($filter_values_validated) {
                foreach ($filter_values_validated as $value) {
                    $query->orWhereJsonContains('category_specification', $value);
                }
            });
        }
    }

    private function applyContentTypeFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::CONTENT_TYPES));
        if ($filter_values_validated !== []) {
            $query->where(function ($query) use ($filter_values_validated) {
                foreach ($filter_values_validated as $value) {
                    $query->orWhereJsonContains('content_type', $value);
                }
            });
        }
    }

    private function applyContentLanguageFilter(Builder $query, array $filter_value): void
    {
        $query->whereIn('content_language', $filter_value);
    }

    private function applyCategoryFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::STATEMENT_CATEGORIES));
        if ($filter_values_validated !== []) {
            $query->whereIn('category', $filter_values_validated);
        }
    }

    private function applyCreatedAtStartFilter(Builder $query, string $filter_value): void
    {
        $date = Carbon::createFromFormat('d-m-Y H:i:s', $filter_value.' 00:00:00');
        $query->where('created_at', '>=', $date);
    }

    private function applyCreatedAtEndFilter(Builder $query, string $filter_value): void
    {
        $date = Carbon::createFromFormat('d-m-Y H:i:s', $filter_value.' 23:59:59');
        $query->where('created_at', '<=', $date);
    }

    private function applyTerritorialScopeFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, EuropeanCountriesService::EUROPEAN_COUNTRY_CODES);
        if ($filter_values_validated !== []) {
            $query->where(function ($query) use ($filter_values_validated) {
                foreach ($filter_values_validated as $value) {
                    $query->orWhereJsonContains('territorial_scope', $value);
                }
            });
        }
    }
}
