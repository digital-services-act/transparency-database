<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\PlatformQueryService;
use App\Services\StatementSearchService;
use App\Services\TokenService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
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

    public function index(Request $request): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $platform_ids_methods_data = $this->statement_search_service->methodsByPlatformAll();

        $filters = [];
        $filters['s'] = $request->get('s');
        $filters['vlop'] = $request->get('vlop');
        $filters['onboarded'] = $request->get('onboarded');
        $filters['has_tokens'] = $request->get('has_tokens');
        $filters['has_statements'] = $request->get('has_statements');

        // Get the platforms.
        $platforms = $this->platform_query_service->query($filters)->with('users', 'users.roles', 'users.tokens');
        $platforms->orderBy('name');
        $platforms = $platforms->paginate(10)->withQueryString();
        $options = $this->prepareOptions();

        return view('onboarding.index', [
            'platform_ids_methods_data' => $platform_ids_methods_data,
            'platforms' => $platforms,
            'options' => $options,
        ]);
    }

    private function prepareOptions(): array
    {
        $vlops = [
            [
                'label' => 'VLOPs',
                'value' => 1
            ],
            [
                'label' => 'Non-Vlops',
                'value' => 0
            ],
            [
                'label' => 'All Platforms',
                'value' => -1
            ],
        ];
        $onboardeds = [
            [
                'label' => 'Yes',
                'value' => 1
            ],
            [
                'label' => 'No',
                'value' => 0
            ],
            [
                'label' => 'All Platforms',
                'value' => -1
            ],
        ];
        $has_tokens = [
            [
                'label' => 'Yes',
                'value' => 1
            ],
            [
                'label' => 'No',
                'value' => 0
            ],
            [
                'label' => 'All Platforms',
                'value' => -1
            ],
        ];
        $has_statements = [
            [
                'label' => 'Yes',
                'value' => 1
            ],
            [
                'label' => 'No',
                'value' => 0
            ],
            [
                'label' => 'All Platforms',
                'value' => -1
            ]
        ];
        return [
            'vlops' => $vlops,
            'onboardeds' => $onboardeds,
            'has_tokens' => $has_tokens,
            'has_statements' => $has_statements,
        ];
    }
}
