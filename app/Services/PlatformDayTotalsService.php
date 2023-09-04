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

    public function dayCountsForRange(Platform $platform, Carbon $start, Carbon $end, string $attribute = '*', string $value = '*', bool $reverse = true): array
    {
        $date_counts = [];

        $start->hour = 0;
        $start->minute = 0;
        $start->second = 0;

        $end->hour = 0;
        $end->minute = 0;
        $end->second = 0;

        while($start < $end) {

            $date_counts[] = [
                'date' => $start->clone(),
                'count' => (int)$this->getDayTotal($platform, $start, $attribute, $value)
            ];

            $start->addDay();
        }

        $highest = -1;
        foreach($date_counts as $date_count)
        {
            if ($date_count['count'] > $highest)
            {
                $highest = $date_count['count'];
            }
        }

        foreach ($date_counts as $index => $date_count)
        {
            $date_counts[$index]['percentage'] = $highest != 0 ? ((int) ceil( ($date_count['count'] / $highest) * 100 )) : 0;
        }

        if ($reverse) {
            $date_counts = array_reverse($date_counts);
        }

        return $date_counts;
    }

    private function monthRange(int $month, int $year)
    {
        $start_date = Carbon::createFromDate($year, $month, 1);
        $end_date = $start_date->clone();
        $end_date->addDays($start_date->daysInMonth);
        return [$start_date, $end_date];
    }
    public function monthCountsForRange(Platform $platform, Carbon $start, Carbon $end, string $attribute = '*', string $value = '*', bool $reverse = true): array
    {
        $month_counts = [];

        $start->hour = 0;
        $start->minute = 0;
        $start->second = 0;

        $end->hour = 0;
        $end->minute = 0;
        $end->second = 0;

        while($start < $end) {

            $range = $this->monthRange($start->month, $start->year);

            $month_counts[] = [
                'month' => $start->clone(),
                'count' => (int)$this->totalForRange($platform, $range[0], $range[1], $attribute, $value)
            ];

            $start->addMonth();
        }

        $highest = -1;
        foreach($month_counts as $month_count)
        {
            if ($month_count['count'] > $highest)
            {
                $highest = $month_count['count'];
            }
        }

        foreach ($month_counts as $index => $month_count)
        {
            $date_counts[$index]['percentage'] = $highest != 0 ? ((int) ceil( ($month_count['count'] / $highest) * 100 )) : 0;
        }

        if ($reverse) {
            $month_counts = array_reverse($month_counts);
        }

        return $month_counts;
    }

    public function topXPlatforms(int $limit = 5, Carbon $start, Carbon $end, string $attribute = '*', string $value = '*')
    {
        return DB::table('platform_day_totals')
                 ->selectRaw('platforms.name, SUM(total) as total')
                 ->whereNotNull('platform_id')
                 ->where('date', '>=', $start->format('Y-m-d 00:00:00'))
                 ->where('date', '<=', $end->format('Y-m-d 00:00:00'))
                 ->where('attribute', $attribute)
                 ->where('value', $value)
                 ->groupBy('platform_id')
                 ->orderBy('total', 'desc')
                 ->join('platforms', 'platform_id', '=', 'platforms.id')
                 ->limit($limit)
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
}