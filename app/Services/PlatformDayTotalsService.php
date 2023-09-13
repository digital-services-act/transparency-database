<?php

namespace App\Services;

use App\Jobs\CompilePlatformDayTotal;
use App\Models\Platform;
use App\Models\PlatformDayTotal;
use App\Models\Statement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    public function deleteDayTotal(Platform $platform, Carbon $date, string $attribute = '*', string $value = '*')
    {
        $platform->dayTotals()->whereDate('date', $date)->where('attribute', $attribute)->where('value', $value)->delete();
    }

    public function deleteAllDayTotals(Platform $platform)
    {
        $platform->dayTotals()->delete();
    }

    public function deleteDayTotals(Platform $platform, string $attribute = '*', string $value = '*')
    {
        $platform->dayTotals()->where('attribute', $attribute)->where('value', $value)->delete();
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
     *
     * @return int
     *
     * This should only be called by a queued job
     */
    public function compileDayTotal(Platform $platform, Carbon $date, string $attribute = '*', string $value = '*'): int
    {
        // We never compile day totals for the future or today...
        // and we are not going to go older than 2020-01-01
        $today = Carbon::now();
        $today->hour = 0;
        $today->minute = 0;
        $today->second = 0;

        if (! ($date < $today) || ! ($date > Carbon::createFromFormat('Y-m-d', '2020-01-01'))) {
            return false;
        }


        $existing = $this->getDayTotal($platform, $date, $attribute, $value);
        if ($existing !== false) {
            return $existing;
        }

        $start = $date->clone();
        $end = $date->clone();

        $start->hour = 0;
        $start->minute = 0;
        $start->second = 0;

        $end->hour = 23;
        $end->minute = 59;
        $end->second = 59;

        $query = Statement::query()->where('platform_id', $platform->id)->where('created_at', '>=', $start)->where('created_at', '<=', $end);

        if ($attribute !== '*' && $value !== '*') {
            $query->where($attribute, $value);
        }

        if ($attribute !== '*' && $value === '*') {
            $query->whereNotNull($attribute);
        }

        $total = $query->count();

        PlatformDayTotal::create([
            'date' => $date,
            'platform_id' => $platform->id,
            'attribute' => $attribute,
            'value' => $value,
            'total' => $total
        ]);

        return $total;
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
                 ->whereNotNull('platform_id')
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
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
                 ->whereNotNull('platform_id')
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
                 ->whereNotNull('platform_id')
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
                 ->whereNotNull('platform_id')
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
                 ->whereNotNull('platform_id')
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', 'category')
                 ->groupBy('value')
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

        $platform_last_days_ago = $this->totalForRange($platform, $start_days, $end);
        $platform_last_months_ago = $this->totalForRange($platform, $start_months, $end);

        $date_counts         = $this->dayCountsForRange($platform, $start_days, $end);
        $month_counts        = $this->monthCountsForRange($platform, $start_months, $end);

        $date_counts = array_reverse($date_counts);
        $month_counts = array_reverse($month_counts);

        $platform_total = $platform->statements()->count();

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

        return compact(
            'date_counts',
            'month_counts',
            'platform_total',
            'platform_last_months_ago',
            'platform_last_days_ago',
            'day_totals_labels',
            'day_totals_values',
            'month_totals_labels',
            'month_totals_values'
        );
    }

    public function prepareReportForCategory(string $category, int $days = 20, int $months = 12): array
    {
        $start_days_ago   = $days;
        $start_months_ago = $months;

        $start_days   = Carbon::now()->subDays($start_days_ago);
        $start_months = Carbon::now()->subMonths($start_months_ago);
        $end          = Carbon::now();

        $category_last_days_ago   = $this->globalTotalForRange($start_days, $end, 'category', $category);
        $category_last_months_ago = $this->globalTotalForRange($start_months, $end, 'category', $category);

        $date_counts  = $this->globalDayCountsForRange($start_days, $end, 'category', $category);
        $month_counts = $this->globalMonthCountsForRange($start_months, $end, 'category', $category);

        $date_counts  = array_reverse($date_counts);
        $month_counts = array_reverse($month_counts);

        $category_total = Statement::query()->where('category', $category)->count();

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

}