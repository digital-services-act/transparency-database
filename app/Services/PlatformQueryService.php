<?php

namespace App\Services;

use App\Models\Platform;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TypeError;

class PlatformQueryService
{
    private array $allowed_filters = [
        's',
        'vlop',
        'onboarded',
        'has_tokens',
        'has_statements',
    ];

    /**
     * @param array $filters
     *
     * @return Builder
     */
    public function query(array $filters): Builder
    {
        $query = Platform::NonDsa();
        foreach ($this->allowed_filters as $filter_key) {
            if (isset($filters[$filter_key])) {
                $method = sprintf('apply%sFilter', ucfirst(Str::camel($filter_key)));
                try {
                    if (method_exists($this, $method)) {
                        $this->$method($query, $filters[$filter_key]);
                    }
                } catch (TypeError|Exception $e) {
                    Log::error("Log Message Query Service Error", ['exception' => $e]);
                }
            }
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applySFilter(Builder $query, string $filter_value): void
    {
        if ($filter_value) {
            $query->where('name', 'LIKE', '%' . $filter_value . '%');
        }
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applyVlopFilter(Builder $query, string $filter_value): void
    {
        if ($filter_value === '1') {
            $query->where('vlop', 1);
        }
        if (!$filter_value) {
            $query->whereNot('vlop', 1);
        }
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applyOnboardedFilter(Builder $query, string $filter_value): void
    {
        if ($filter_value === '1') {
            $query->where('onboarded', 1);
        }
        if (!$filter_value) {
            $query->whereNot('onboarded', 1);
        }
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applyHasTokensFilter(Builder $query, string $filter_value): void
    {
        if ($filter_value === '1') {
            $query->where('has_tokens', 1);
        }
        if (!$filter_value) {
            $query->whereNot('has_tokens', 1);
        }
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applyHasStatementsFilter(Builder $query, string $filter_value): void
    {
        if ($filter_value === '1') {
            $query->where('has_statements', 1);
        }
        if (!$filter_value) {
            $query->whereNot('has_statements', 1);
        }
    }

    public function updateHasStatements(array $platform_ids, int $has_statements = 1): void
    {
        Platform::query()->whereIn('id', $platform_ids)->update(['has_statements' => $has_statements]);
    }
}
