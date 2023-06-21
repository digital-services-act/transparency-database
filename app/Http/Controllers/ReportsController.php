<?php

namespace App\Http\Controllers;

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

    public function __construct(StatementStatsService $statement_stats_service)
    {
        $this->statement_stats_service = $statement_stats_service;
    }

    /**
     * @throws Exception
     */
    public function index(Request $request): Factory|View|Application
    {
        $platform = $request->user()->platform;

        $days = [
            1,
            7,
            14,
            30,
            60,
            90,
            180,
            365
        ];
        $days_count = [];
        foreach ($days as $days_ago) {
            $days_count[$days_ago] = $this->statement_stats_service->countForPlatformSince($platform, Carbon::now()->subDays($days_ago));
        }


        $start_days_ago = 20;
        $start = Carbon::now()->subDays($start_days_ago);
        $end = Carbon::now();
        $date_counts = $this->statement_stats_service->dayCountsForPlatformAndRange($platform, $start, $end);

        $your_platform_total = $this->statement_stats_service->countForPlatform($platform);

        $total = $this->statement_stats_service->totalStatements();

        return view('reports.index', [
            'days_count' => $days_count,
            'total' => $total,
            'your_platform_total' => $your_platform_total,
            'date_counts' => $date_counts,
            'start_days_ago' => $start_days_ago,
        ]);
    }
}
