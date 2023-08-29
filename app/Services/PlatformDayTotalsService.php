<?php

namespace App\Services;

use App\Jobs\CompilePlatformDayTotal;
use App\Models\Platform;
use App\Models\PlatformDayTotal;
use App\Models\Statement;
use Carbon\Carbon;

class PlatformDayTotalsService
{
    public function getDayTotal(Platform $platform, Carbon $date, string $attribute = '*', string $value = '*'): PlatformDayTotal|bool
    {
        $dayTotal = $platform->dayTotals()->whereDate('date', $date)->where('attribute', $attribute)->where('value', $value)->first() ?? false;
        return $dayTotal;
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
     * @return PlatformDayTotal|bool
     *
     * This should only be called by a queued job
     */
    public function compileDayTotal(Platform $platform, Carbon $date, string $attribute = '*', string $value = '*'): PlatformDayTotal|bool
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

        // Delete any existing.
        $this->deleteDayTotal($platform, $date, $attribute, $value);

        $start = $date->clone();
        $end = $date->clone();

        $start->hour = 0;
        $start->minute = 0;
        $start->second = 0;

        $end->hour = 23;
        $end->minute = 59;
        $end->second = 59;

        $query = Statement::query()->where('platform_id', $platform->id)->whereBetween('created_at', [$start, $end]);

        if ($attribute !== '*' && $value !== '*') {
            $query->where($attribute, $value);
        }

        if ($attribute !== '*' && $value === '*') {
            $query->whereNotNull($attribute);
        }

        $total = $query->count();

        return PlatformDayTotal::create([
            'date' => $date,
            'platform_id' => $platform->id,
            'attribute' => $attribute,
            'value' => $value,
            'total' => $total
        ]);
    }
}