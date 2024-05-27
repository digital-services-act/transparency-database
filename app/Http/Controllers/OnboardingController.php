<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\StatementSearchService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{

    public function __construct(protected StatementSearchService $statement_search_service)
    {
    }

    public function index(Request $request)
    {
        $vlop_count = Platform::Vlops()->count();
        $platforms = Platform::nonVlops()->with('users')->orderBy('name')->get();
        $total_platforms_sending = $this->statement_search_service->totalPlatformsSending();

        return view('onboarding.index', [
            'platforms' => $platforms,
            'vlop_count' => $vlop_count,
            'total_platforms_sending' => $total_platforms_sending
        ]);

    }


}
