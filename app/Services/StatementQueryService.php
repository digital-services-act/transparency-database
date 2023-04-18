<?php

namespace App\Services;

use App\Models\Statement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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

        if ($filters['automated_detection'] ?? null) {
            $statements->whereIn('automated_detection', $filters['automated_detection']);
        }

        if ($filters['automated_takedown'] ?? null) {
            $statements->whereIn('automated_takedown', $filters['automated_takedown']);
        }

        if ($filters['decision_ground'] ?? null) {
            $statements->whereIn('decision_ground', $filters['decision_ground']);
        }

        if ($filters['platform_type'] ?? null) {
            $statements->whereIn('platform_type', $filters['platform_type']);
        }

        if ($filters['created_at_start'] ?? null) {
            // ECL sends in the date as d-m-Y
            $statements->where('created_at', '>=', Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_start'] . ' 00:00:00'));
        }

        if ($filters['created_at_end'] ?? null) {
            // ECL sends in the date as d-m-Y
            $statements->where('created_at', '<=', Carbon::createFromFormat('d-m-Y H:i:s', $filters['created_at_end'] . ' 23:59:59'));
        }

        if ($filters['countries_list'] ?? null) {
            foreach ($filters['countries_list'] as $country) {
                $statements->where('countries_list', 'LIKE', '%"'.$country.'"%');
            }
        }

        if ($filters['s'] ?? null) {
            $statements->whereHas('user', function($query) use($filters)
            {
                $query->where('name', 'LIKE', '%' . $filters['s'] . '%');
            });
        }

        return $statements;
    }
}