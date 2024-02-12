<?php

namespace App\Services;

use App\Models\DayArchive;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class DayArchiveQueryService
{

    protected $builder;

    private array $allowed_filters = [
        'uuid',
        'from_date',
        'to_date'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(array $filters): Builder
    {
        $dayarchives = DayArchive::query()->whereNotNull('completed_at');
        foreach ($this->allowed_filters as $filter_key) {
            if (isset($filters[$filter_key]) && $filters[$filter_key]) {
                $method = sprintf('apply%sFilter', ucfirst(Str::camel($filter_key)));
                try {
                    if (method_exists($this, $method)) {
                        $this->$method($dayarchives, $filters[$filter_key]);
                    }
                } catch (\TypeError|\Exception $e) {
                    Log::error("Day Archive Query Service Error: " . $e->getMessage());
                }
            } else {
                $method = sprintf('applyMissing%sFilter', ucfirst(Str::camel($filter_key)));
                try {
                    if (method_exists($this, $method)) {
                        $this->$method($dayarchives);
                    }
                } catch (\TypeError|\Exception $e) {
                    Log::error("Day Archive Query Service Error: " . $e->getMessage());
                }
            }
        }

        return $dayarchives;
    }

    /**
     * @param array $filter_value
     * @return void
     */
    private function applyUuidFilter(Builder $query, string $filter_value): void
    {
        Log::info('uuid: '. $filter_value);
        $platform = Platform::query()->where('uuid', $filter_value)->first();
        if ($platform){
            $query->where('platform_id', $platform->id);
        } else {
            $this->applyMissingUuidFilter($query);
        }

    }

    private function applyMissingUuidFilter(Builder $query): void
    {
        $query->whereNull('platform_id');
    }

    /**
     *
     * @return void
     */
    private function applyFromDateFilter(Builder $query, string $filter_value): void
    {
        $date = Carbon::createFromFormat('d-m-Y', $filter_value);
        $query->whereDate('date', '>=', $date);
    }

    /**
     *
     * @return void
     */
    private function applyToDateFilter(Builder $query, string $filter_value): void
    {
        $date = Carbon::createFromFormat('d-m-Y', $filter_value);
        $query->whereDate('date', '<=', $date);
    }


}
