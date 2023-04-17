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
            $statements->where('created_at', '>=', Carbon::createFromFormat('d-m-Y', $filters['created_at_start']));
        }

        if ($filters['created_at_end'] ?? null) {
            $statements->where('created_at', '<=', Carbon::createFromFormat('d-m-Y', $filters['created_at_end']));
        }

        if ($filters['countries_list'] ?? null) {
            foreach ($filters['countries_list'] as $country) {
                $statements->where('countries_list', 'LIKE', '%"'.$country.'"%');
            }
        }

        if ($filters['s'] ?? null) {
            $statements->where(function($query) use($statements, $filters) {
                $query->where('uuid', 'like', '%' . $filters['s'] . '%')->orWhereHas('user', function($query_inner) use($filters)
                {
                    $query_inner->where('name', 'LIKE', '%' . $filters['s'] . '%');
                });
            });
        }

        return $statements;
    }
}