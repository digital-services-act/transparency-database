<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TypeError;
use Yoeriboven\LaravelLogDb\Models\LogMessage;

class LogMessageQueryService
{
    private array $allowed_filters = [
        's'
    ];

    /**
     * @param array $filters
     *
     * @return Builder
     */
    public function query(array $filters): Builder
    {
        $query = LogMessage::query();
        foreach ($this->allowed_filters as $filter_key) {
            if (isset($filters[$filter_key]) && $filters[$filter_key]) {
                $method = sprintf('apply%sFilter', ucfirst(Str::camel($filter_key)));
                if (method_exists($this, $method)) {
                    $this->$method($query, $filters[$filter_key]);
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
        if (filter_var($filter_value, FILTER_VALIDATE_INT)) {
            $filter_value = (int)$filter_value;
            $query->where('id', $filter_value);
        } else {
            $query->orWhere('message', 'LIKE', '%' . $filter_value . '%');
            $query->orWhere('context', 'LIKE', '%' . $filter_value . '%');
        }
    }
}
