<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Models\Platform;
use App\Services\StatementSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OnboardingController extends Controller
{

    public function __construct(protected StatementSearchService $statement_search_service)
    {
    }

    public function index(Request $request)
    {
        $vlop_count = Platform::Vlops()->count();
        $platforms = Platform::nonVlops()->with('users')->orderBy('name')->get();
        $total_vlop_platforms_sending = $this->statement_search_service->totalVlopPlatformsSending();
        $total_non_vlop_platforms_sending = $this->statement_search_service->totalNonVlopPlatformsSending();
        $total_valid_tokens = PersonalAccessToken::query()->orWhereNull('expires_at')->orwhere('expires_at', '>=', Carbon::now())->count();

        return view('onboarding.index', [
            'platforms' => $platforms,
            'vlop_count' => $vlop_count,
            'total_vlop_platforms_sending' => $total_vlop_platforms_sending,
            'total_non_vlop_platforms_sending' => $total_non_vlop_platforms_sending,
            'total_valid_tokens' =>  $total_valid_tokens
        ]);

    }


}
