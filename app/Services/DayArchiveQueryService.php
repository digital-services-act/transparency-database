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
    private array $allowed_filters = [
        'uuid',
        'from_date',
        'to_date',
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
        if (! isset($filters['uuid']) || ! $filters['uuid']) {
            $query->whereNull('platform_id');
        }

        return $query;
    }

    private function applyUuidFilter(Builder $query, string $filter_value): void
    {
        $platform = Platform::query()->where('uuid', $filter_value)->first();
        if ($platform) {
            $query->where('platform_id', $platform->id);
        }
    }

    private function applyFromDateFilter(Builder $query, string $filter_value): void
    {
        $date = Carbon::createFromFormat('d-m-Y', $filter_value);
        $query->whereDate('date', '>=', $date);
    }

    private function applyToDateFilter(Builder $query, string $filter_value): void
    {
        $date = Carbon::createFromFormat('d-m-Y', $filter_value);
        $query->whereDate('date', '<=', $date);
    }
}
