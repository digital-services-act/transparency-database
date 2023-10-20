<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\Statement;
use App\Services\PlatformDayTotalsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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
        //To avoid division by zero if no non-DSA platforms are defined
        $platforms_total = max(1, Platform::nonDsa()->count());
        $average_per_hour = intval($total_last_days / ($last_days * 24));
        $average_per_hour_per_platform = intval((($total_last_days / ($last_days * 24)) / $platforms_total));


        $total = $this->platform_day_totals_service->globalStatementsTotal();

        // Don't do any query count on the statement table
        // very slow, use the service
        //Statement::query()->whereRaw("created_at < ?", [$midnight])->count();

        $top_x = 5;
        $top_platforms = $this->platform_day_totals_service->topPlatforms(Carbon::now()->subDays($last_days), Carbon::now());
        $top_platforms = array_slice($top_platforms, 0, $top_x);
        $top_categories = $this->platform_day_totals_service->topCategories(Carbon::now()->subDays($last_days), Carbon::now());
        $top_categories = array_slice($top_categories, 0, $top_x);

        $last_history_days = 30;
        $day_totals = $this->platform_day_totals_service->globalDayCountsForRange(Carbon::now()->subDays($last_history_days), Carbon::now());

        $day_totals = collect($day_totals)->sortBy('date')->toArray();


        $day_totals_values = array_map(function ($item) {
            return $item->total;
        }, $day_totals);

        $day_totals_labels = array_map(function ($item) {
            return $item->date;
        }, $day_totals);



        return view('analytics.index', compact(
            'total',
            'last_days',
            'last_months',
            'total_last_days',
            'total_last_months',
            'average_per_hour',
            'platforms_total',
            'average_per_hour_per_platform',
            'top_x',
            'top_platforms',
            'top_categories',
            'day_totals',
            'day_totals_values',
            'day_totals_labels',
            'last_history_days'
        ));
    }

    public function platforms(Request $request)
    {
        $platforms_total = max(1, Platform::nonDsa()->count());
        $last_days = 90;

        $platform_totals = [];
        $platforms = Platform::nonDsa()->get();


        foreach ($platforms as $platform) {

            $platform_totals[] = [
                'name' => $platform->name,
                'total' => (int)$this->platform_day_totals_service->totalForRange($platform, Carbon::now()->subDays($last_days), Carbon::now())
            ];

        }

        uasort($platform_totals, function ($a, $b) {
            if ($a['total'] === $b['total']) {
                return 0;
            }
            return $a['total'] < $b['total'] ? 1 : -1;
        });

        $platform_totals_values = array_map(function ($item) {
            return $item['total'];
        }, $platform_totals);

        $platform_totals_labels = array_map(function ($item) {
            return $item['name'];
        }, $platform_totals);

        $options = $this->prepareOptions();

        return view('analytics.platforms', compact(
            'last_days',
            'platforms_total',
            'platform_totals_values',
            'platform_totals_labels',
            'options'
        ));
    }

    public function forPlatform(Request $request, string $uuid = ''): Application|View|Factory|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $days_ago = 20;
        $months_ago = 12;
        $platform = false;
        $platform_report = false;
        if ($uuid) {
            /** @var Platform $platform */
            $platform = Platform::query()->where('uuid', $uuid)->first();
            if (!$platform) {
                return redirect(route('analytics.platforms'));
            }
            $platform_report = $this->platform_day_totals_service->prepareReportForPlatform($platform, $days_ago, $months_ago);
            $platform_report['top_categories'] = array_slice($platform_report['top_categories'], 0, 5);
        }

        $options = $this->prepareOptions();

        return view('analytics.platform', compact(
            'platform',
            'platform_report',
            'options',
            'days_ago',
            'months_ago'
        ));
    }

    public function forCategory(Request $request, string $category = ''): Application|View|Factory|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $days_ago = 20;
        $months_ago = 12;

        if (!$category || !isset($category, Statement::STATEMENT_CATEGORIES[$category])) {
            return redirect(route('analytics.categories'));
        }

        $category_report = $this->platform_day_totals_service->prepareReportForCategory($category);

        $category_report['top_platforms'] = array_slice($category_report['top_platforms'], 0, 5);

        $options = $this->prepareOptions();

        return view('analytics.category', compact(
            'category',
            'category_report',
            'options',
            'days_ago',
            'months_ago'
        ));
    }

    public function forPlatformCategory(Request $request): Application|View|Factory|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $uuid = $request->get('uuid', '');
        $category = $request->get('category', '');

        $days_ago = 20;
        $months_ago = 12;
        $platform = false;
        $platform_category_report = false;

        if ($uuid) {
            /** @var Platform $platform */
            $platform = Platform::query()->where('uuid', $uuid)->first();
            if (!$platform) {
                return redirect(route('analytics.platform-category'));
            }
        }
        if ($category && !isset($category, Statement::STATEMENT_CATEGORIES[$category])) {
            return redirect(route('analytics.platform-category'));
        }

        if ($platform && $category) {
            $platform_category_report = $this->platform_day_totals_service->prepareReportForPlatformCategory($platform, $category);
        }

        $options = $this->prepareOptions();

        return view('analytics.platform-category', compact(
            'platform',
            'category',
            'platform_category_report',
            'options',
            'days_ago',
            'months_ago'
        ));
    }

    public function forKeyword(Request $request, string $keyword = ''): Application|View|Factory|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $days_ago = 20;
        $months_ago = 12;

        if (!$keyword || !isset($keyword, Statement::KEYWORDS[$keyword])) {
            return redirect(route('analytics.keywords'));
        }

        $keyword_report = $this->platform_day_totals_service->prepareReportForKeyword($keyword);

        $options = $this->prepareOptions();

        return view('analytics.keyword', compact(
            'keyword',
            'keyword_report',
            'options',
            'days_ago',
            'months_ago'
        ));
    }

    public function restrictions()
    {
        $last_days = 90;

        $restrictions_data['decision_visibility'] = $this->computeTotalsWithKeyLabel(Statement::DECISION_VISIBILITIES, 'decision_visibility', $last_days, true);
        $restrictions_data['decision_monetary'] = $this->computeTotalsWithKeyLabel(Statement::DECISION_MONETARIES, 'decision_monetary', $last_days, true);
        $restrictions_data['decision_provision'] = $this->computeTotalsWithKeyLabel(Statement::DECISION_PROVISIONS, 'decision_provision', $last_days, true);
        $restrictions_data['decision_account'] = $this->computeTotalsWithKeyLabel(Statement::DECISION_ACCOUNTS, 'decision_account', $last_days, true);

        return view('analytics.restrictions', compact(
            'last_days',
            'restrictions_data'
        ));
    }

    public function categories(Request $request)
    {
        $last_days = 90;

        $category_totals = [];
        $categories = Statement::STATEMENT_CATEGORIES;

        foreach ($categories as $category_key => $category_label) {

            $total = $this->platform_day_totals_service->globalTotalForRange(Carbon::now()->subDays($last_days), Carbon::now(), 'category', $category_key);
            if ($request->query('chaos')) {
                $chaos = abs((int)$request->query('chaos'));
                $total += random_int(($chaos * -1), $chaos);
            }

            $category_totals[] = [
                'name' => $category_label,
                'total' => (int)$total
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

        $category_totals_values = array_map(function ($item) {
            return $item['total'];
        }, $category_totals);

        $category_totals_labels = array_map(function ($item) {
            return $item['name'];
        }, $category_totals);

        $options = $this->prepareOptions();

        return view('analytics.categories', compact(
            'last_days',
            'category_totals',
            'category_totals_labels',
            'category_totals_values',
            'options'
        ));
    }

    public function keywords(Request $request)
    {
        $last_days = 90;

        $keyword_totals = [];
        $keywords = Statement::KEYWORDS;

        foreach ($keywords as $keyword_key => $keyword_label) {

            $total = $this->platform_day_totals_service->globalTotalForRange(Carbon::now()->subDays($last_days), Carbon::now(), 'category_specification', $keyword_key);


            $keyword_totals[] = [
                'name' => $keyword_label,
                'total' => (int)$total
            ];

        }

        if ($request->query('sort')) {
            uasort($keyword_totals, function ($a, $b) {
                if ($a['total'] === $b['total']) {
                    return 0;
                }

                return $a['total'] < $b['total'] ? 1 : -1;
            });
        }

        $keyword_totals_values = array_map(function ($item) {
            return $item['total'];
        }, $keyword_totals);

        $keyword_totals_labels = array_map(function ($item) {
            return $item['name'];
        }, $keyword_totals);

        $options = $this->prepareOptions();

        return view('analytics.keywords', compact(
            'last_days',
            'keyword_totals',
            'keyword_totals_labels',
            'keyword_totals_values',
            'options'
        ));
    }

    public function grounds(Request $request)
    {
        $last_days = 90;

        $ground_totals = [];
        $grounds = Statement::DECISION_GROUNDS;

        foreach ($grounds as $ground_key => $ground_label) {

            $total = $this->platform_day_totals_service->globalTotalForRange(Carbon::now()->subDays($last_days), Carbon::now(), 'decision_ground', $ground_key);
            if ($request->query('chaos')) {
                $chaos = abs((int)$request->query('chaos'));
                $total += random_int(($chaos * -1), $chaos);
            }

            $ground_totals[] = [
                'name' => $ground_label,
                'total' => (int)$total
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
        $ground_totals_values = array_map(function ($item) {
            return $item['total'];
        }, $ground_totals);

        $ground_totals_labels = array_map(function ($item) {
            return $item['name'];
        }, $ground_totals);

        return view('analytics.grounds', compact(
            'last_days',
            'ground_totals',
            'ground_totals_labels',
            'ground_totals_values'
        ));
    }

    private function prepareOptions(): array
    {
        $platforms = Platform::nonDsa()->orderBy('name')->get()->map(function ($platform) {
            return [
                'value' => $platform->uuid,
                'label' => $platform->name
            ];
        })->toArray();

        $categories = $this->mapForSelectWithKeys(Statement::STATEMENT_CATEGORIES);
        $keywords = $this->mapForSelectWithKeys(Statement::KEYWORDS);

        return compact(
            'platforms',
            'categories',
            'keywords'
        );
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
