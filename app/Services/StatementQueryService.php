<?php

namespace App\Services;

use App\Models\Statement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StatementQueryService
{
    // These are the filters that we are allowed to filter on.
    // If there is to be a new filter, then add it here first and then make
    // a function

    private array $allowed_filters = [
        's',
        'automated_detection',
        'automated_takedown',
        'created_at_start',
        'created_at_end',
        'decision_ground',
        'platform_type',
        'countries_list',
    ];

    /**
     * @param array $filters
     *
     * @return Builder
     */
    public function query(array $filters): Builder
    {
        $statements = Statement::query();

        foreach ($this->allowed_filters as $filter_key)
        if (isset($filters[$filter_key]) && $filters[$filter_key]) {
            $method = 'apply' . ucfirst(Str::camel($filter_key))     . 'Filter';
            try {
                $this->$method($statements, $filters[$filter_key]);
            }
            catch (\TypeError|\Exception $e) {
                Log::error("Statement Query Service Error: " . $e->getMessage());
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
        $query->whereHas('user', function($inner_query) use($filter_value)
        {
            $inner_query->where('name', 'LIKE', '%' . $filter_value . '%');
        });
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
    private function applyAutomatedTakedownFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, Statement::AUTOMATED_TAKEDOWNS);
        if ($filter_values_validated) {
            $query->whereIn('automated_takedown', $filter_values_validated);
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
    private function applyPlatformTypeFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, array_keys(Statement::PLATFORM_TYPES));
        if ($filter_values_validated) {
            $query->whereIn('platform_type', $filter_value);
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