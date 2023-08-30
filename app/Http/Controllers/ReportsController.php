<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\PlatformDayTotal;
use App\Services\PlatformDayTotalsService;
use App\Services\StatementSearchService;
use App\Services\StatementStatsService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    protected StatementStatsService $statement_stats_service;
    protected StatementSearchService $statement_search_service;
    protected PlatformDayTotalsService $platform_day_totals_service;

    public function __construct(
        StatementStatsService $statement_stats_service,
        StatementSearchService $statement_search_service,
        PlatformDayTotalsService $platform_day_totals_service
    )
    {
        $this->statement_stats_service = $statement_stats_service;
        $this->statement_search_service = $statement_search_service;
        $this->platform_day_totals_service = $platform_day_totals_service;
    }

    /**
     * @throws Exception
     */
    public function index(Request $request): Factory|View|Application
    {
        $platform = $request->user()->platform;

        if (!$platform) {
            return redirect(route('dashboard'))->withErrors('You may not view reports until your account is belonging to a platform.');
        }

        $days_ago = 20;
        $months_ago = 12;

        $platform_report = $this->prepareReportForPlatform($platform, $days_ago, $months_ago);

        return view('reports.index', [

            'platform' => $platform,
            'platform_report' => $platform_report,
            'days_ago' => $days_ago,
            'months_ago' => $months_ago

        ]);
    }

    public function forPlatform(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $platform = null;
        $platform_report = [];
        $platform_id = (int)$request->query('platform_id', null);
        if ($platform_id) {
            $platform = Platform::find($platform_id);
        }

        $days_ago = 20;
        $months_ago = 12;

        if ($platform) {
            $platform_report = $this->prepareReportForPlatform($platform, $days_ago, $months_ago);
        }

        $options = $this->prepareOptions();

        return view('reports.for-platform', [
            'options' => $options,
            'platform' => $platform,
            'platform_report' => $platform_report,
            'days_ago' => $days_ago,
            'months_ago' => $months_ago
        ]);

    }

    private function prepareReportForPlatform(Platform $platform, int $days = 20, int $months = 12): array
    {
        $start_days_ago = $days;
        $start_months_ago = $months;

        $date_counts = [];
        $month_counts = [];
        $platform_total = 0;
        $platform_last_days_ago = 0;
        $platform_last_months_ago = 0;

        $start_days = Carbon::now()->subDays($start_days_ago);
        $start_months = Carbon::now()->subMonths($start_months_ago);
        $end = Carbon::now();

        $platform_last_days_ago = $this->platform_day_totals_service->totalForRange($platform, $start_days, $end);
        $platform_last_months_ago = $this->platform_day_totals_service->totalForRange($platform, $start_months, $end);

        $date_counts         = $this->platform_day_totals_service->dayCountsForRange($platform, $start_days, $end);
        $month_counts        = $this->platform_day_totals_service->monthCountsForRange($platform, $start_months, $end);

        $platform_total = $platform->statements()->count();

        return compact(
            'date_counts',
            'month_counts',
            'platform_total',
            'platform_last_months_ago',
            'platform_last_days_ago',
        );
    }

    private function prepareOptions(): array
    {
        $platforms = Platform::query()->orderBy('name', 'ASC')->get()->map(function($platform){
            return [
                'value' => $platform->id,
                'label' => $platform->name
            ];
        })->toArray();

        return compact(
            'platforms',
        );
    }
}
