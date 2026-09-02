<?php

namespace App\Services;

use App\Models\DayArchive;
use App\Models\Platform;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TypeError;

class DayArchiveQueryService
{
    private const string DATE_FILTER_FORMAT = 'd-m-Y';

    private array $allowed_filters = [
        'platform_id',
        'from_date',
        'to_date',
        'uuid',
    ];

    public function query(array $filters): Builder
    {
        // Only completed archives.
        $query = DayArchive::query()->whereNotNull('completed_at');

        foreach ($this->allowed_filters as $filter_key) {
            if (isset($filters[$filter_key]) && $filters[$filter_key]) {
                $method = sprintf('apply%sFilter', ucfirst(Str::camel($filter_key)));
                try {
                    if (method_exists($this, $method)) {
                        $this->$method($query, $filters[$filter_key]);
                    }
                } catch (TypeError|Exception $e) {
                    Log::error('Day Archive Query Service Error', ['exception' => $e]);
                }
            }
        }

        // if there was no uuid filter then lock it into the global archives
        if (! isset($filters['platform_id']) || ! $filters['platform_id']) {
            $query->whereNull('platform_id');
        }

        return $query;
    }

    private function applyUuidFilter(Builder $query, mixed $filter_value): void
    {
        if (! is_string($filter_value) || ! Str::isUuid($filter_value)) {
            return;
        }

        $platform = Platform::query()->where('uuid', $filter_value)->first();
        if ($platform) {
            $query->where('platform_id', $platform->id);
        }
    }

    private function applyFromDateFilter(Builder $query, mixed $filter_value): void
    {
        $date = $this->parseDateFilter($filter_value);
        if (! $date) {
            return;
        }

        $query->whereDate('date', '>=', $date);
    }

    private function applyToDateFilter(Builder $query, mixed $filter_value): void
    {
        $date = $this->parseDateFilter($filter_value);
        if (! $date) {
            return;
        }

        $query->whereDate('date', '<=', $date);
    }

    private function parseDateFilter(mixed $filter_value): ?Carbon
    {
        if (! is_string($filter_value)) {
            return null;
        }

        $filter_value = trim($filter_value);
        if (! preg_match('/^\d{2}-\d{2}-\d{4}$/', $filter_value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!'.self::DATE_FILTER_FORMAT, $filter_value);
        // @codeCoverageIgnoreStart
        } catch (Exception) {
            return null;
        // @codeCoverageIgnoreEnd
        }

        return $date->format(self::DATE_FILTER_FORMAT) === $filter_value ? $date : null;
    }

    private function applyPlatformIdFilter(Builder $query, $value): void
    {
        $platform = Platform::query()->where('id', $value)->count();
        if ($platform) {
            $query->where('platform_id', $value);
        }
    }
}
