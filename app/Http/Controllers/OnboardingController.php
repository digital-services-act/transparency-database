<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\PlatformQueryService;
use App\Services\StatementSearchService;
use App\Services\TokenService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    protected TokenService $tokenService;
    protected StatementSearchService $statement_search_service;
    protected PlatformQueryService $platform_query_service;

    public function __construct(PlatformQueryService $platform_query_service, StatementSearchService $statement_search_service, TokenService $tokenService)
    {
        $this->platform_query_service = $platform_query_service;
        $this->statement_search_service = $statement_search_service;
        $this->tokenService = $tokenService;
    }

    public function index(Request $request)
    {
        // Establish the counts.
        $vlop_count = Platform::Vlops()->count();
        $non_vlop_count = Platform::nonVlops()->count();
        $total_vlop_platforms_sending = $this->statement_search_service->totalVlopPlatformsSending();
        $total_vlop_platforms_sending_api = $this->statement_search_service->totalVlopPlatformsSendingApi();
        $total_vlop_platforms_sending_webform = $this->statement_search_service->totalVlopPlatformsSendingWebform();
        $total_non_vlop_platforms_sending = $this->statement_search_service->totalNonVlopPlatformsSending();
        $total_non_vlop_platforms_sending_api = $this->statement_search_service->totalNonVlopPlatformsSendingApi();
        $total_non_vlop_platforms_sending_webform = $this->statement_search_service->totalNonVlopPlatformsSendingWebform();
        $total_vlop_valid_tokens = $this->tokenService->getTotalVlopValidTokens();
        $total_non_vlop_valid_tokens = $this->tokenService->getTotalNonVlopValidTokens();

        $filters = [];
        $filters['s'] = $request->get('s', '');
        $filters['vlop'] = $request->get('vlop', 0);
        $filters['onboarded'] = $request->get('onboarded', 0);

        // Get the platforms.
        $platforms = $this->platform_query_service->query($filters)->with('users');
        $platforms->orderBy('name');
        $platforms = $platforms->paginate(10)->withQueryString();
        $options = $this->prepareOptions();

        return view('onboarding.index', [
            'platforms' => $platforms,
            'options' => $options,
            'vlop_count' => $vlop_count,
            'non_vlop_count' => $non_vlop_count,
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

    private function prepareOptions(): array
    {
        $vlops = [
            [
                'label' => 'Yes',
                'value' => 1
            ],
            [
                'label' => 'No',
                'value' => 0
            ]
        ];
        $onboardeds = [
            [
                'label' => 'Yes',
                'value' => 1
            ],
            [
                'label' => 'No',
                'value' => 0
            ]
        ];
        return [
            'vlops' => $vlops,
            'onboardeds' => $onboardeds,
        ];
    }
}
