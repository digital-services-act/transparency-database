<?php

namespace App\Http\Controllers;
use App\Models\Platform;
use App\Models\Statement;
use App\Services\PlatformDayTotalsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    protected PlatformDayTotalsService $platform_day_totals_service;

    public function __construct(PlatformDayTotalsService $platform_day_totals_service)
    {
        $this->platform_day_totals_service = $platform_day_totals_service;
    }

    public function index(Request $request)
    {
        $last_days = 20;
        $last_months = 12;
        $total_last_days = $this->platform_day_totals_service->globalTotalForRange(Carbon::now()->subDays($last_days), Carbon::now());
        $total_last_months = $this->platform_day_totals_service->globalTotalForRange(Carbon::now()->subMonths($last_months), Carbon::now());
        $platforms_total = Platform::nonDsa()->count();
        $average_per_hour = number_format(($total_last_days / ($last_days + 24)), 2);
        $average_per_hour_per_platform = number_format((($total_last_days / ($last_days + 24)) / $platforms_total), 2);

        $total = Statement::count();


        return view('analytics.index', compact(
            'total',
            'last_days',
            'last_months',
            'total_last_days',
            'total_last_months',
            'average_per_hour',
            'platforms_total',
            'average_per_hour_per_platform'
        ));
    }

    public function platforms(Request $request)
    {
        $platforms_total = Platform::count();
        $last_days = 90;

        $platform_totals = [];
        $platforms = Platform::nonDsa()->get();

        foreach ($platforms as $platform)
        {

            $platform_totals[] = [
                'name'  => $platform->name,
                'total' => $this->platform_day_totals_service->totalForRange($platform, Carbon::now()->subDays($last_days), Carbon::now())
            ];

        }

        uasort($platform_totals, function($a, $b){
            if ($a['total'] === $b['total']) {
                return 0;
            }
            return $a['total'] < $b['total'] ? 1 : -1;
        });

        return view('analytics.platforms', compact(
            'last_days',
            'platforms_total',
            'platform_totals'
        ));
    }

    public function restrictions(Request $request)
    {
        $last_days = 90;

        $restriction_totals = [];
        $restrictions = [
            'decision_visibility' => 'Visibility',
            'decision_monetary' => 'Monetary',
            'decision_provision' => 'Provision',
            'decision_account' => 'Account'
        ];



        foreach ($restrictions as $attribute => $restriction)
        {

            $total = $this->platform_day_totals_service->globalTotalForRange(Carbon::now()->subDays($last_days), Carbon::now(), $attribute);
            if ($request->query('chaos')) {
                $chaos = abs((int)$request->query('chaos'));
                $total += random_int(($chaos * -1), $chaos);
            }

            $restriction_totals[] = [
                'name'  => $restriction,
                'total' => $total
            ];

        }

        if ($request->query('sort')) {
            uasort($restriction_totals, function ($a, $b) {
                if ($a['total'] === $b['total']) {
                    return 0;
                }

                return $a['total'] < $b['total'] ? 1 : -1;
            });
        }

        return view('analytics.restrictions', compact(
            'last_days',
            'restriction_totals'
        ));
    }

    public function categories(Request $request)
    {
        $last_days = 90;

        $category_totals = [];
        $categories = Statement::STATEMENT_CATEGORIES;

        foreach ($categories as $category_key => $category_label)
        {

            $total = $this->platform_day_totals_service->globalTotalForRange(Carbon::now()->subDays($last_days), Carbon::now(), 'category', $category_key);
            if ($request->query('chaos')) {
                $chaos = abs((int)$request->query('chaos'));
                $total += random_int(($chaos * -1), $chaos);
            }

            $category_totals[] = [
                'name'  => $category_label,
                'total' => $total
            ];

        }

        if ($request->query('sort')) {
            uasort($category_totals, function ($a, $b) {
                if ($a['total'] === $b['total']) {
                    return 0;
                }

                return $a['total'] < $b['total'] ? 1 : -1;
            });
        }

        return view('analytics.categories', compact(
            'last_days',
            'category_totals'
        ));
    }

    public function grounds(Request $request)
    {
        $last_days = 90;

        $ground_totals = [];
        $grounds = Statement::DECISION_GROUNDS;

        foreach ($grounds as $ground_key => $ground_label)
        {

            $total = $this->platform_day_totals_service->globalTotalForRange(Carbon::now()->subDays($last_days), Carbon::now(), 'decision_ground', $ground_key);
            if ($request->query('chaos')) {
                $chaos = abs((int)$request->query('chaos'));
                $total += random_int(($chaos * -1), $chaos);
            }

            $ground_totals[] = [
                'name'  => $ground_label,
                'total' => $total
            ];

        }

        if ($request->query('sort')) {
            uasort($ground_totals, function ($a, $b) {
                if ($a['total'] === $b['total']) {
                    return 0;
                }

                return $a['total'] < $b['total'] ? 1 : -1;
            });
        }

        return view('analytics.grounds', compact(
            'last_days',
            'ground_totals'
        ));
    }
}
