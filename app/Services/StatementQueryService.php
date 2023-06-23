<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StatementQueryService
{
    // These are the filters that we are allowed to filter on.
    // If there is to be a new filter, then add it here first and then make
    // a function. new_attribute -> applyNewAttributeFilter()

    private array $allowed_filters = [
        's',
        'platform_id',
        'platform_type',
        'automated_detection',
        'automated_decision',
        'created_at_start',
        'created_at_end',
        'decision_ground',
        'decision_visibility',
        'decision_monetary',
        'decision_provision',
        'decision_account',
        'category',
        'content_type',
        'countries_list',
        'source_type'
    ];

    /**
     * @param array $filters
     *
     * @return Builder
     */
    public function query(array $filters): Builder
    {
        $statements = Statement::query();
        foreach ($this->allowed_filters as $filter_key) {
            if (isset($filters[$filter_key]) && $filters[$filter_key]) {
                $method = sprintf('apply%sFilter', ucfirst(Str::camel($filter_key)));
                try {
                    $this->$method($statements, $filters[$filter_key]);
                } catch (\TypeError|\Exception $e) {
                    Log::error("Statement Query Service Error: " . $e->getMessage());
                }
            }
        }

        return $statements;
    }


    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applySFilter(Builder $query, string $filter_value): void
    {
        $ids = Statement::search($filter_value)->take(200)->raw()['results']->pluck('id')->toArray();
        $query->whereIn('id', $ids);
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyPlatformIdFilter(Builder $query, array $filter_value): void
    {
        $query->whereHas('platform', function($inner_query) use($filter_value) {
            $inner_query->whereIn('platforms.id', $filter_value);
        });
    }


    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applySourceTypeFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::SOURCE_TYPES));
        if ($filter_values_validated) {
            $query->whereIn('source_type', $filter_values_validated);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyAutomatedDetectionFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, Statement::AUTOMATED_DETECTIONS);
        if ($filter_values_validated) {
            $query->whereIn('automated_detection', $filter_values_validated);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyAutomatedDecisionFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, Statement::AUTOMATED_DECISIONS);
        if ($filter_values_validated) {
            $query->whereIn('automated_decision', $filter_values_validated);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyDecisionGroundFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_GROUNDS));
        if ($filter_values_validated) {
            $query->whereIn('decision_ground', $filter_value);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyDecisionVisibilityFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_VISIBILITIES));
        if ($filter_values_validated) {
            $query->whereIn('decision_visibility', $filter_value);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyDecisionMonetaryFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_MONETARIES));
        if ($filter_values_validated) {
            $query->whereIn('decision_monetary', $filter_value);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyDecisionProvisionFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_PROVISIONS));
        if ($filter_values_validated) {
            $query->whereIn('decision_provision', $filter_value);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyDecisionAccountFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::DECISION_ACCOUNTS));
        if ($filter_values_validated) {
            $query->whereIn('decision_account', $filter_value);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyContentTypeFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::CONTENT_TYPES));
        if ($filter_values_validated) {
            $query->whereIn('content_type', $filter_value);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyCategoryFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::STATEMENT_CATEGORIES));
        if ($filter_values_validated) {
            $query->whereIn('category', $filter_value);
        }
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyPlatformTypeFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Platform::PLATFORM_TYPES));
        if ($filter_values_validated) {
            $query->whereHas('platform', function($inner_query) use($filter_values_validated) {
                $inner_query->whereIn('platforms.type', $filter_values_validated);
            });
        }


    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applyCreatedAtStartFilter(Builder $query, string $filter_value): void
    {
        $date = Carbon::createFromFormat('d-m-Y H:i:s', $filter_value . ' 00:00:00');
        $query->where('created_at', '>=', $date);
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applyCreatedAtEndFilter(Builder $query, string $filter_value): void
    {
        $date = Carbon::createFromFormat('d-m-Y H:i:s', $filter_value . ' 23:59:59');
        $query->where('created_at', '<=', $date);
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    private function applyCountriesListFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, Statement::EUROPEAN_COUNTRY_CODES);
        if ($filter_values_validated) {
            foreach ($filter_values_validated as $country) {
                $query->where('countries_list', 'LIKE', '%"' . $country . '"%');
            }
        }
    }
}
