<?php

namespace App\Http\Controllers;

use App\Models\Statement;
use App\Services\StatementSearchService;
use App\Services\StatementStatsService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{
    protected StatementStatsService $statement_stats_service;
    protected StatementSearchService $statement_search_service;

    public function __construct(StatementStatsService $statement_stats_service, StatementSearchService $statement_search_service)
    {
        $this->statement_stats_service = $statement_stats_service;
        $this->statement_search_service = $statement_search_service;
    }

    /**
     * @throws Exception
     */
    public function index(Request $request): Factory|View|Application
    {
        $platform = $request->user()->platform;

        $start_days_ago = 20;
        $start = Carbon::now()->subDays($start_days_ago);
        $end = Carbon::now();

        $date_counts = [];
        $your_platform_total = 0;
        $total = 0;

        if (config('scout.driver') == 'opensearch') {
            $date_counts = $this->statement_search_service->dayCountsForPlatformAndRange($platform, $start, $end);
            $your_platform_total = $this->statement_search_service->countForPlatform($platform);
            $total = $this->statement_search_service->totalStatements();
        } else {
            $date_counts = $this->statement_stats_service->dayCountsForPlatformAndRange($platform, $start, $end);
            $your_platform_total = $this->statement_stats_service->countForPlatform($platform);
            $total = $this->statement_stats_service->totalStatements();
        }


        return view('reports.index', [

            'total' => $total,
            'your_platform_total' => $your_platform_total,
            'date_counts' => $date_counts,
            'start_days_ago' => $start_days_ago

        ]);
    }
}
