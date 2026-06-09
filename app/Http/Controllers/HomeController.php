<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\StatementElasticStatsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(protected StatementElasticStatsService $statement_elastic_stats_service) {}

    public function index(Request $request): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $one_day = 60 * 60 * 25;

        $total = $this->statement_elastic_stats_service->grandTotal();
        $platforms_total = Cache::remember('platforms_total', $one_day, static fn () => max(1, Platform::nonDsa()->count()));

        $top_x = 3;

        $top_categories = $this->statement_elastic_stats_service->topCategories();
        $top_categories = array_slice($top_categories, 0, $top_x);

        $top_decisions_visibility = $this->statement_elastic_stats_service->topDecisionVisibilities();
        $top_decisions_visibility = array_slice($top_decisions_visibility, 0, $top_x);

        $automated_decision_percentage = $this->statement_elastic_stats_service->fullyAutomatedDecisionPercentage();

        return view('home', [
            'total' => $total,
            'platforms_total' => $platforms_total,
            'top_categories' => $top_categories,
            'top_decisions_visibility' => $top_decisions_visibility,
            'automated_decision_percentage' => $automated_decision_percentage,
        ]);
    }
}
