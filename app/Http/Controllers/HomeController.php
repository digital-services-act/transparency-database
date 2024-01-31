<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\StatementSearchService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Random\RandomException;

class HomeController extends Controller
{
    protected StatementSearchService $statement_search_service;

    public function __construct(
        StatementSearchService $statement_search_service
    )
    {
        $this->statement_search_service = $statement_search_service;
    }

    /**
     * @param Request $request
     *
     * @return View|Application|Factory|\Illuminate\Contracts\Foundation\Application
     * @throws RandomException
     */
    public function index(Request $request): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $last_days = 30;

        $total = $this->statement_search_service->grandTotal();
        $platforms_total = Cache::remember('platforms_total', 60*60*24, function() {
            return max(1, Platform::nonDsa()->count());
        });

        $top_x = 3;

        $top_categories = $this->statement_search_service->topCategories();
        $top_categories = array_slice($top_categories, 0, $top_x);

        $top_decisions_visibility = $this->statement_search_service->topDecisionVisibilities();
        $top_decisions_visibility = array_slice($top_decisions_visibility, 0, $top_x);

        $automated_decision_percentage = $this->statement_search_service->fullyAutomatedDecisionPercentage();

        return view('home', compact(
            'total',
            'platforms_total',
            'top_categories',
            'top_decisions_visibility',
            'automated_decision_percentage'
        ));
    }
}
