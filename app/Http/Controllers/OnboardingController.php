<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\StatementSearchService;
use App\Services\TokenService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    protected $tokenService;

    public function __construct(StatementSearchService $statement_search_service, TokenService $tokenService)
    {
        $this->statement_search_service = $statement_search_service;
        $this->tokenService = $tokenService;
    }

    public function index(Request $request)
    {
        $vlop_count = Platform::Vlops()->count();
        $platforms = Platform::nonVlops()->with('users')->orderBy('name')->get();
        $total_vlop_platforms_sending = $this->statement_search_service->totalVlopPlatformsSending();
        $total_vlop_platforms_sending_api = $this->statement_search_service->totalVlopPlatformsSendingApi();
        $total_vlop_platforms_sending_webform = $this->statement_search_service->totalVlopPlatformsSendingWebform();
        $total_non_vlop_platforms_sending = $this->statement_search_service->totalNonVlopPlatformsSending();
        $total_non_vlop_platforms_sending_api = $this->statement_search_service->totalNonVlopPlatformsSendingApi();
        $total_non_vlop_platforms_sending_webform = $this->statement_search_service->totalNonVlopPlatformsSendingWebform();
        $total_vlop_valid_tokens = $this->tokenService->getTotalVlopValidTokens();
        $total_non_vlop_valid_tokens = $this->tokenService->getTotalNonVlopValidTokens();

        return view('onboarding.index', [
            'platforms' => $platforms,
            'vlop_count' => $vlop_count,
            'total_vlop_platforms_sending' => $total_vlop_platforms_sending,
            'total_vlop_platforms_sending_api' => $total_vlop_platforms_sending_api,
            'total_vlop_platforms_sending_webform' => $total_vlop_platforms_sending_webform,
            'total_non_vlop_platforms_sending' => $total_non_vlop_platforms_sending,
            'total_non_vlop_platforms_sending_api' => $total_non_vlop_platforms_sending_api,
            'total_non_vlop_platforms_sending_webform' => $total_non_vlop_platforms_sending_webform,
            'total_vlop_valid_tokens' => $total_vlop_valid_tokens,
            'total_non_vlop_valid_tokens' => $total_non_vlop_valid_tokens,
        ]);
    }
}
