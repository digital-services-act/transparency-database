<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\Statement;
use App\Services\PlatformDayTotalsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    protected PlatformDayTotalsService $platform_day_totals_service;

    public function __construct(PlatformDayTotalsService $platform_day_totals_service)
    {
        $this->platform_day_totals_service = $platform_day_totals_service;
    }

    public function index(Request $request)
    {
        $last_days = 30;

        $total = $this->platform_day_totals_service->globalStatementsTotal();
        $platforms_total = max(1, Platform::nonDsa()->count());

        $top_x = 3;
        $top_categories = $this->platform_day_totals_service->topCategories(Carbon::now()->subDays($last_days), Carbon::now());
        $top_categories = array_slice($top_categories, 0, $top_x);

        $decisions_visibility = $this->computeTotalsWithKeyLabel(Statement::DECISION_VISIBILITIES, 'decision_visibility', $last_days, true);
        ksort($decisions_visibility['labels']);
        $top_decisions_visibility = array_slice($decisions_visibility['labels'],0,3);




        return view('home', compact(
            'total',
            'platforms_total',
            'top_categories',
            'top_decisions_visibility'
        ));
    }

    /**
     * @param array $return_data
     * @param array $keyValueArray
     * @param int $last_days
     * @param string $attribute
     * @param bool $sort
     * @return array
     */
    public function computeTotalsWithKeyLabel(array $keyValueArray, string $attribute, int $last_days, bool $sort = false)
    {
        foreach ($keyValueArray as $restriction_key => $restriction_label) {

            $total = $this->platform_day_totals_service->globalTotalForRange(Carbon::now()->subDays($last_days), Carbon::now(), $attribute, $restriction_key);

            $totals[] = [
                'name' => $restriction_label,
                'total' => (int)$total
            ];

        }

        if ($sort) {
            uasort($totals, function ($a, $b) {
                if ($a['total'] === $b['total']) {
                    return 0;
                }

                return $a['total'] < $b['total'] ? 1 : -1;
            });
        }

        $return_data = [];

        $return_data['values'] = array_map(function ($item) {
            return $item['total'];
        }, $totals);

        $return_data['labels'] = array_map(function ($item) {
            return $item['name'];
        }, $totals);

        return $return_data;

    }
}
