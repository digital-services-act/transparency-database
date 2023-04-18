<?php

namespace App\Services;

use App\Models\Statement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class StatementQueryService
{
    /**
     * @param array $filters
     *
     * @return Builder
     */
    public function query(array $filters): Builder
    {
        $statements = Statement::query();

        foreach ($filters as $filter_key => $filter_value) {
            $method = 'apply' . ucfirst(Str::camel($filter_key))     . 'Filter';
            if (method_exists($this, $method) && $filter_value) {
                try {
                    $this->$method($statements, $filter_value);
                } catch (\TypeError $e) {

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
    public function applySFilter(Builder $query, string $filter_value): void
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
    public function applyAutomatedDetectionFilter(Builder $query, array $filter_value): void
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
    public function applyAutomatedTakedownFilter(Builder $query, array $filter_value): void
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
    public function applyDecisionGroundFilter(Builder $query, array $filter_value): void
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
    public function applyPlatformTypeFilter(Builder $query, array $filter_value): void
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
    public function applyCreatedAtStartFilter(Builder $query, string $filter_value): void
    {
        try {
            $date = Carbon::createFromFormat('d-m-Y H:i:s', $filter_value . ' 00:00:00');
            $query->where('created_at', '>=', $date);
        } catch (\Exception $e) {}
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    public function applyCreatedAtEndFilter(Builder $query, string $filter_value): void
    {
        try {
            $date = Carbon::createFromFormat('d-m-Y H:i:s', $filter_value . ' 23:59:59');
            $query->where('created_at', '<=', $date);
        } catch (\Exception $e) {}
    }

    /**
     * @param Builder $query
     * @param array $filter_value
     *
     * @return void
     */
    public function applyCountriesListFilter(Builder $query, array $filter_value): void
    {
        $filter_values_validated = array_intersect($filter_value, Statement::EUROPEAN_COUNTRY_CODES);
        if ($filter_values_validated) {
            foreach ($filter_values_validated as $country) {
                $query->where('countries_list', 'LIKE', '%"' . $country . '"%');
            }
        }
    }
}