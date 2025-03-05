<?php

namespace App\Services;

use App\Models\Platform;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
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

    public const ONE_HOUR = 3600;

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
                } catch (TypeError | Exception $e) {
                    Log::error("Platform Query Service Error", ['exception' => $e]);
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
    private function applyVlopFilter(Builder $query, int $filter_value): void
    {
        if ($filter_value === 1) {
            $query->where('vlop', 1);
        }
        if ($filter_value === 0) {
            $query->where('vlop', 0);
        }
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applyOnboardedFilter(Builder $query, int $filter_value): void
    {
        if ($filter_value === 1) {
            $query->where('onboarded', 1);
        }
        if ($filter_value === 0) {
            $query->where('onboarded', 0);
        }
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applyHasTokensFilter(Builder $query, int $filter_value): void
    {
        if ($filter_value === 1) {
            $query->where('has_tokens', 1);
        }
        if ($filter_value === 0) {
            $query->where('has_tokens', 0);
        }
    }

    /**
     * @param Builder $query
     * @param string $filter_value
     *
     * @return void
     */
    private function applyHasStatementsFilter(Builder $query, int $filter_value): void
    {
        if ($filter_value === 1) {
            $query->where('has_statements', 1);
        }
        if ($filter_value === 0) {
            $query->where('has_statements', 0);
        }
    }

    public function updateHasStatements(array $platform_ids, int $has_statements = 1): void
    {
        Platform::query()->whereIn('id', $platform_ids)->update(['has_statements' => $has_statements]);
    }

    public function getPlatformDropDownOptions(): array
    {
        return Cache::remember('platform-dropdown-options', self::ONE_HOUR, callback: function () {
            return Platform::nonDsa()
                ->selectRaw('id as value, name as label')
                ->orderBy('name', 'ASC')
                ->get()
                ->toArray();
        });
    }

    public function getPlatformsById(): array
    {
        return Cache::remember('platforms-by-id', self::ONE_HOUR, function (): array {
            return Platform::nonDsa()->pluck('name', 'id')->toArray();
        });
    }

    public function getPlatformIds(): array
    {
        return Cache::remember('platform-ids', self::ONE_HOUR, function (): array {
            return Platform::nonDsa()->pluck('id')->toArray();
        });
    }

    public function getVlopPlatformIds(): array
    {
        return Cache::remember('vlop-ids', self::ONE_HOUR, function () {
            return Platform::Vlops()->pluck('id')->toArray();
        });
    }
}
