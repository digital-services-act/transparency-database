<?php

namespace App\Services;

use App\Jobs\CompilePlatformDayTotal;
use App\Models\Platform;
use App\Models\PlatformDayTotal;
use App\Models\Statement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlatformDayTotalsService
{
    public function getDayTotal(Platform $platform, Carbon $date, string $attribute = '*', string $value = '*'): int|bool
    {
        return DB::table('platform_day_totals')
            ->select('total')
            ->where('platform_id', $platform->id)
            ->where('date', $date->format('Y-m-d 00:00:00'))
            ->where('attribute', $attribute)
            ->where('value', $value)
            ->first()->total ?? false;
    }

    public function deleteDayTotal(Platform $platform, Carbon $date, string $attribute = '*', string $value = '*'): void
    {
        $query = $platform->dayTotals()->whereDate('date', $date);
        if ($attribute !== '*' ) {
            $query->where('attribute', $attribute);
        }
        if ($value !== '*') {
            $query->where('value', $value);
        }
        $query->delete();
    }

    public function deleteAllDayTotals(Platform $platform)
    {
        $platform->dayTotals()->delete();
    }

    public function deleteDayTotals(Platform $platform, string $attribute = '*', string $value = '*')
    {
        $platform->dayTotals()->where('attribute', $attribute)->where('value', 'LIKE', $value)->delete();

    }

    /**
     * @param Platform $platform
     * @param string $attribute
     * @param string $value
     *
     * @return void
     *
     * Queue all the possible days needed for platform's statements
     */
    public function compileDayTotals(Platform $platform, string $attribute = '*', string $value = '*', int $days_ago = 1)
    {
        /** @var Carbon $start */
        $start = Carbon::now();
        if ($days_ago >= 1) {
            $start = Carbon::now()->subDays($days_ago);
        } else {
            $start = $platform->statements()->orderBy('created_at', 'ASC')->first()->created_at ?? Carbon::now();
        }


        $end = Carbon::now();
        $end->hour = 0;
        $end->minute = 0;
        $end->second = 0;

        while($start < $end) {
            CompilePlatformDayTotal::dispatch($platform->id, $start->format('Y-m-d'), $attribute, $value);
            $start->addDay();
        }
    }

    /**
     * @param Platform $platform
     * @param Carbon $date
     * @param string $attribute
     * @param string $value
     * @param bool $delete_existing
     *
     * @return int
     *
     * This should only be called by a queued job
     */
    public function compileDayTotal(Platform $platform, Carbon $date, string $attribute = '*', string $value = '*', bool $delete_existing = false): int
    {

        // This function should only be called in the context of a queued job.

        // We never compile day totals for the future or today...
        // and we are not going to go older than 2020-01-01
        $today = Carbon::now();
        $today->hour = 0;
        $today->minute = 0;
        $today->second = 0;

        if (! ($date < $today) || ! ($date > Carbon::createFromFormat('Y-m-d', '2020-01-01'))) {
            return false;
        }

        if ($delete_existing) {
            $this->deleteDayTotal($platform, $date, $attribute, $value);
        }

        $existing = $this->getDayTotal($platform, $date, $attribute, $value);

        if ($existing !== false) {
            return $existing;
        }

        if (config('scout.driver') != 'opensearch') {

            $start = $date->clone();
            $end = $date->clone();

            $start->hour = 0;
            $start->minute = 0;
            $start->second = 0;

            $end->hour = 23;
            $end->minute = 59;
            $end->second = 59;

            // CRITICAL OBSERVATION
            // This is the whole potato of doing the stats and analytics here.
            // If this is slow, then we need to drop down to a raw query
            // If a raw query is slow then it's game over.
            $query = Statement::query()->where('platform_id', $platform->id)->where('created_at', '>=', $start)->where('created_at', '<=', $end);

            if ($attribute !== '*' && $value !== '*') {
                $query->where($attribute, 'LIKE', '%'.$value.'%');
            }

            if ($attribute !== '*' && $value === '*') {
                $query->whereNotNull($attribute);
            }

            $total = $query->count(); // <- this guy right here is the Achilles' heel
            // END OBSERVATION

            PlatformDayTotal::create([
                'date' => $date,
                'platform_id' => $platform->id,
                'attribute' => $attribute,
                'value' => $value,
                'total' => $total
            ]);

            return $total;
        }

        $s = $date->format('Y-m-d') . 'T00:00:00';
        $e = $date->format('Y-m-d') . 'T23:59:59';
        $opensearch_query  = 'platform_id:' . $platform->id;
        $opensearch_query .= ' AND created_at:['.$s.' TO '.$e.']';

        if ($attribute !== '*' && $value !== '*') {
            $opensearch_query .= ' AND ' . $attribute . ':' . $value;
        }
        if ($attribute !== '*' && $value === '*') {
            $opensearch_query .= ' AND ' . $attribute . ':*';
        }

        //dd($filters);
        $total = Statement::search($opensearch_query)->options([
            'track_total_hits' => true
        ])->paginate(1)->total();

        PlatformDayTotal::create([
            'date' => $date,
            'platform_id' => $platform->id,
            'attribute' => $attribute,
            'value' => $value,
            'total' => $total
        ]);

        return $total;
    }

    public function globalStatementsTotal(): int
    {
        $end = Carbon::now();
        return DB::table('platform_day_totals')
                 ->selectRaw('SUM(total) as total')
                 ->where('platform_id', '!=', 0)
                 ->where('date', '>=', '2020-01-01 00:00:00')
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', '*')
                 ->where('value', '*')
                 ->first()->total ?? 0;
    }

    public function totalForRange(Platform $platform, Carbon $start, Carbon $end, string $attribute = '*', string $value = '*'): int|bool
    {
        return DB::table('platform_day_totals')
            ->selectRaw('SUM(total) as total')
            ->where('platform_id', $platform->id)
            ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
            ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
            ->where('attribute', $attribute)
            ->where('value', $value)
            ->first()->total ?? false;
    }

    public function globalTotalForRange(Carbon $start, Carbon $end, string $attribute = '*', string $value = '*'): int|bool
    {

        return DB::table('platform_day_totals')
                 ->selectRaw('SUM(total) as total')
                 ->where('platform_id', '!=', 0)
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', $attribute)
                 ->where('value', $value)
                 ->first()->total ?? false;
    }

    public function globalTotalForDate(Carbon $date,  string $attribute = '*', string $value = '*'): int|bool
    {
        return DB::table('platform_day_totals')
                 ->selectRaw('SUM(total) as total')
                 ->where('platform_id', '!=', 0)
                 ->where('date', '=', $date->format('Y-m-d 00:00:00'))
                 ->where('attribute', $attribute)
                 ->where('value', $value)
                 ->first()->total ?? false;
    }

    public function dayCountsForRange(Platform $platform, Carbon $start, Carbon $end, string $attribute = '*', string $value = '*'): array
    {
        return DB::table('platform_day_totals')
                 ->selectRaw('date, SUM(total) as total')
                 ->where('platform_id', $platform->id)
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', $attribute)
                 ->where('value', $value)
                 ->groupBy('date')
                 ->get()->toArray();
    }

    public function globalDayCountsForRange(Carbon $start, Carbon $end, string $attribute = '*', string $value = '*'): array
    {
        return DB::table('platform_day_totals')
                 ->selectRaw('date, SUM(total) as total')
                 ->where('platform_id', '!=', 0)
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', $attribute)
                 ->where('value', $value)
                 ->groupBy('date')
                 ->get()->toArray();
    }

    public function monthCountsForRange(Platform $platform, Carbon $start, Carbon $end, string $attribute = '*', string $value = '*'): array
    {
        return DB::table('platform_day_totals')
                 ->selectRaw("CONCAT(YEAR(platform_day_totals.date), '-', MONTH(platform_day_totals.date)) as month, SUM(total) as total")
                 ->where('platform_id', $platform->id)
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', $attribute)
                 ->where('value', $value)
                 ->groupByRaw("month")
                 ->get()->toArray();
    }

    public function globalMonthCountsForRange(Carbon $start, Carbon $end, string $attribute = '*', string $value = '*'): array
    {
        return DB::table('platform_day_totals')
                 ->selectRaw("CONCAT(YEAR(platform_day_totals.date), '-', MONTH(platform_day_totals.date)) as month, SUM(total) as total")
                 ->where('platform_id', '!=', 0)
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', $attribute)
                 ->where('value', $value)
                 ->groupByRaw("month")
                 ->get()->toArray();
    }

    public function topPlatforms(Carbon $start, Carbon $end, string $attribute = '*', string $value = '*')
    {
        return DB::table('platform_day_totals')
                 ->selectRaw('platforms.name, platforms.uuid, SUM(total) as total')
                 ->where('platform_id', '!=', 0)
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', $attribute)
                 ->where('value', $value)
                 ->groupBy('platform_id')
                 ->orderBy('total', 'desc')
                 ->join('platforms', 'platform_id', '=', 'platforms.id')
                 ->get()->toArray();
    }

    public function topCategories(Carbon $start, Carbon $end)
    {
        return DB::table('platform_day_totals')
                 ->selectRaw('value, SUM(total) as total')
                 ->where('platform_id', '!=', 0)
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', 'category')
                 ->groupBy('value')
                 ->orderBy('total', 'desc')
                 ->get()->toArray();
    }

    public function topCategoriesPlatform(Platform $platform, Carbon $start, Carbon $end)
    {
        return DB::table('platform_day_totals')
                 ->selectRaw('value, SUM(total) as total')
                 ->where('platform_id', '=', $platform->id)
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', 'category')
                 ->groupBy('value')
                 ->orderBy('total', 'desc')
                 ->get()->toArray();
    }

    public function topPlatformsCategory(string $category, Carbon $start, Carbon $end)
    {
        return DB::table('platform_day_totals')
                 ->selectRaw('platform_id, name, uuid, SUM(total) as total')
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', 'category')
                 ->where('value', $category)
                 ->join('platforms', 'platform_day_totals.platform_id', 'platforms.id')
                 ->groupBy('platform_id')
                 ->orderBy('total', 'desc')
                 ->get()->toArray();
    }

    public function prepareReportForPlatform(Platform $platform, int $days = 20, int $months = 12): array
    {
        $start_days_ago = $days;
        $start_months_ago = $months;

        $start_days = Carbon::now()->subDays($start_days_ago);
        $start_months = Carbon::now()->subMonths($start_months_ago);
        $end = Carbon::now();
        $beginning = Carbon::createFromDate(2020, 1, 1);

        $platform_last_days_ago = $this->totalForRange($platform, $start_days, $end);
        $platform_last_months_ago = $this->totalForRange($platform, $start_months, $end);

        $date_counts         = $this->dayCountsForRange($platform, $start_days, $end);
        $month_counts        = $this->monthCountsForRange($platform, $start_months, $end);

        $date_counts = collect($date_counts)->sortBy('date')->toArray();
        $month_counts = collect($month_counts)->sortBy('date')->toArray();

        $platform_total = $this->totalForRange($platform, $beginning, $end);

        $day_totals_values = array_map(function($item){
            return $item->total;
        }, $date_counts);

        $day_totals_labels = array_map(function($item){
            return $item->date;
        }, $date_counts);

        $month_totals_values = array_map(function($item){
            return $item->total;
        }, $month_counts);

        $month_totals_labels = array_map(function($item){
            return $item->month;
        }, $month_counts);

        $top_categories = $this->topCategoriesPlatform($platform, $start_days, $end);

        return compact(
            'date_counts',
            'month_counts',
            'platform_total',
            'platform_last_months_ago',
            'platform_last_days_ago',
            'day_totals_labels',
            'day_totals_values',
            'month_totals_labels',
            'month_totals_values',
            'top_categories'
        );
    }

    public function prepareReportForCategory(string $category, int $days = 20, int $months = 12): array
    {
        $start_days_ago   = $days;
        $start_months_ago = $months;

        $start_days   = Carbon::now()->subDays($start_days_ago);
        $start_months = Carbon::now()->subMonths($start_months_ago);
        $end          = Carbon::now();
        $beginning = Carbon::createFromDate(2020, 1, 1);

        $category_last_days_ago   = $this->globalTotalForRange($start_days, $end, 'category', $category);
        $category_last_months_ago = $this->globalTotalForRange($start_months, $end, 'category', $category);

        $date_counts  = $this->globalDayCountsForRange($start_days, $end, 'category', $category);
        $month_counts = $this->globalMonthCountsForRange($start_months, $end, 'category', $category);

        $date_counts = collect($date_counts)->sortBy('date')->toArray();
        $month_counts = collect($month_counts)->sortBy('date')->toArray();

        $category_total = $this->globalTotalForRange($beginning, $end, 'category', $category);

        $day_totals_values = array_map(function ($item) {
            return $item->total;
        }, $date_counts);

        $day_totals_labels = array_map(function ($item) {
            return $item->date;
        }, $date_counts);

        $month_totals_values = array_map(function ($item) {
            return $item->total;
        }, $month_counts);

        $month_totals_labels = array_map(function ($item) {
            return $item->month;
        }, $month_counts);

        $top_platforms = $this->topPlatformsCategory($category, $start_days, $end);

        return compact(
            'date_counts',
            'month_counts',
            'category_total',
            'category_last_months_ago',
            'category_last_days_ago',
            'day_totals_labels',
            'day_totals_values',
            'month_totals_labels',
            'month_totals_values',
            'top_platforms'
        );
    }

    public function prepareReportForPlatformCategory(Platform $platform, string $category, int $days = 20, int $months = 12)
    {
        $start_days_ago   = $days;
        $start_months_ago = $months;

        $start_days   = Carbon::now()->subDays($start_days_ago);
        $start_months = Carbon::now()->subMonths($start_months_ago);
        $end          = Carbon::now();
        $beginning = Carbon::createFromDate(2020, 1, 1);

        $category_last_days_ago   = $this->totalForRange($platform, $start_days, $end, 'category', $category);
        $category_last_months_ago = $this->totalForRange($platform, $start_months, $end, 'category', $category);

        $date_counts  = $this->dayCountsForRange($platform, $start_days, $end, 'category', $category);
        $month_counts = $this->monthCountsForRange($platform, $start_months, $end, 'category', $category);

        $date_counts = collect($date_counts)->sortBy('date')->toArray();
        $month_counts = collect($month_counts)->sortBy('date')->toArray();

        $category_total = $this->totalForRange($platform, $beginning, $end, 'category', $category);

        $day_totals_values = array_map(function ($item) {
            return $item->total;
        }, $date_counts);

        $day_totals_labels = array_map(function ($item) {
            return $item->date;
        }, $date_counts);

        $month_totals_values = array_map(function ($item) {
            return $item->total;
        }, $month_counts);

        $month_totals_labels = array_map(function ($item) {
            return $item->month;
        }, $month_counts);

        return compact(
            'date_counts',
            'month_counts',
            'category_total',
            'category_last_months_ago',
            'category_last_days_ago',
            'day_totals_labels',
            'day_totals_values',
            'month_totals_labels',
            'month_totals_values'
        );
    }

    public function prepareReportForKeyword(string $keyword, int $days = 20, int $months = 12): array
    {
        $start_days_ago   = $days;
        $start_months_ago = $months;

        $start_days   = Carbon::now()->subDays($start_days_ago);
        $start_months = Carbon::now()->subMonths($start_months_ago);
        $end          = Carbon::now();
        $beginning = Carbon::createFromDate(2020, 1, 1);

        $keyword_last_days_ago   = $this->globalTotalForRange($start_days, $end, 'category_specification', $keyword);
        $keyword_last_months_ago = $this->globalTotalForRange($start_months, $end, 'category_specification', $keyword);

        $date_counts  = $this->globalDayCountsForRange($start_days, $end, 'category_specification', $keyword);
        $month_counts = $this->globalMonthCountsForRange($start_months, $end, 'category_specification', $keyword);

        $date_counts = collect($date_counts)->sortBy('date')->toArray();
        $month_counts = collect($month_counts)->sortBy('date')->toArray();

        $keyword_total = $this->globalTotalForRange($beginning, $end, 'category_specification', $keyword);

        $day_totals_values = array_map(function ($item) {
            return $item->total;
        }, $date_counts);

        $day_totals_labels = array_map(function ($item) {
            return $item->date;
        }, $date_counts);

        $month_totals_values = array_map(function ($item) {
            return $item->total;
        }, $month_counts);

        $month_totals_labels = array_map(function ($item) {
            return $item->month;
        }, $month_counts);

        return compact(
            'date_counts',
            'month_counts',
            'keyword_total',
            'keyword_last_months_ago',
            'keyword_last_days_ago',
            'day_totals_labels',
            'day_totals_values',
            'month_totals_labels',
            'month_totals_values'
        );
    }

}
