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

        $sorting_query_base = "?" . http_build_query($filters);

        $allowed_orderbys = [
            'name',
            'created_at'
        ];

        $allowed_directions = [
            'asc',
            'desc'
        ];

        $sorting = $request->get('sorting', 'name:asc');
        $parts = explode(":", $sorting);


        $orderby = $parts[0] ?? $allowed_orderbys[0];
        $direction = $parts[1] ?? $allowed_directions[0];

        // Get the platforms.
        $platforms = $this->platform_query_service->query($filters)->with('users', 'users.roles', 'users.tokens');

        if (in_array($orderby, $allowed_orderbys, true) && in_array($direction, $allowed_directions, true)) {
            $platforms->orderBy($orderby, $direction);
        } else {
            $platforms->orderBy($allowed_orderbys[0], $allowed_directions[0]);
        }

        $platforms = $platforms->paginate(10)->withQueryString();
        $options = $this->prepareOptions();
        $all_platforms_count = Platform::nonDSA()->count();

        $possible_tags = [
            'vlop:1' => 'is VLOP',
            'vlop:0' => 'is Non-VLOP',
            'onboarded:1' => 'onboarded',
            'onboarded:0' => 'not onboarded',
            'has_tokens:0' => 'does not have tokens',
            'has_tokens:1' => 'has tokens',
            'has_statements:0' => 'does not have statements',
            'has_statements:1' => 'has statements',
        ];

        $tags = [];
        foreach ($filters as $filter => $value) {
            $key = $filter . ':' . $value;
            if (isset($possible_tags[$key])) {
                $filters_copy = $filters;
                unset($filters_copy[$filter]);
                $url = '?' . http_build_query($filters_copy) . '&sorting=' . $sorting;
                $tag = [
                    'label' => $possible_tags[$key],
                    'url' => $url,
                    'removable' => true
                ];
                $tags[] = $tag;
            }
        }

        if (isset($filters['s']) && $filters['s'] !== '') {
            $filters_copy = $filters;
            unset($filters_copy['s']);
            $url = '?' . http_build_query($filters_copy) . '&sorting=' . $sorting;
            $tags[] = [
                'label' => 'matching term: "'. htmlentities($filters['s']) .'"',
                'url' => $url,
                'removable' => true
            ];
        }

        return view('onboarding.index', [
            'platform_ids_methods_data' => $platform_ids_methods_data,
            'platforms' => $platforms,
            'options' => $options,
            'all_platforms_count' => $all_platforms_count,
            'sorting_query_base' => $sorting_query_base,
            'tags' => $tags
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
        $sorting = [
            [
                'label' => 'A to Z',
                'value' => 'name:asc'
            ],
            [
                'label' => 'Z to A',
                'value' => 'name:desc'
            ],
            [
                'label' => 'Created New Old',
                'value' => 'created_at:desc'
            ],
            [
                'label' => 'Created Old New',
                'value' => 'created_at:asc'
            ],
        ];
        return [
            'vlops' => $vlops,
            'onboardeds' => $onboardeds,
            'has_tokens' => $has_tokens,
            'has_statements' => $has_statements,
            'sorting' => $sorting
        ];
    }
}
