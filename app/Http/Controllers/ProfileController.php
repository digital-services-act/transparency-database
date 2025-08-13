<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\User;
use App\Services\PlatformQueryService;
use App\Services\StatementElasticSearchService;
use App\Services\TokenService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Laravel\Sanctum\PersonalAccessToken;

class ProfileController extends Controller
{
    protected TokenService $tokenService;
    protected StatementElasticSearchService $statement_elastic_search_service;
    protected PlatformQueryService $platform_query_service;

    public function __construct(PlatformQueryService $platform_query_service, StatementElasticSearchService $statement_elastic_search_service, TokenService $tokenService)
    {
        $this->platform_query_service = $platform_query_service;
        $this->statement_elastic_search_service = $statement_elastic_search_service;
        $this->tokenService = $tokenService;
    }

    /**
     * @param Request $request
     *
     * @return Factory|View|Application
     */
    public function profile(Request $request): Factory|View|Application
    {
        // Get and cache the global platform method data.
        $platform_ids_methods_data = $this->statement_elastic_search_service->methodsByPlatformAll();

        // All calls after this one should be using the cached data.

        $all_sending_platform_ids = $this->statement_elastic_search_service->allSendingPlatformIds();
        $this->platform_query_service->updateHasStatements($all_sending_platform_ids);

        // Establish the counts.
        $vlop_count = Platform::Vlops()->count();
        $non_vlop_count = Platform::nonVlops()->count();

        // Should be coming from the cached opensearch result.
        $total_vlop_platforms_sending = $this->statement_elastic_search_service->totalVlopPlatformsSending();
        $total_vlop_platforms_sending_api = $this->statement_elastic_search_service->totalVlopPlatformsSendingApi();
        $total_vlop_platforms_sending_webform = $this->statement_elastic_search_service->totalVlopPlatformsSendingWebform();
        $total_non_vlop_platforms_sending = $this->statement_elastic_search_service->totalNonVlopPlatformsSending();
        $total_non_vlop_platforms_sending_api = $this->statement_elastic_search_service->totalNonVlopPlatformsSendingApi();
        $total_non_vlop_platforms_sending_webform = $this->statement_elastic_search_service->totalNonVlopPlatformsSendingWebform();

        $total_vlop_valid_tokens = $this->tokenService->getTotalVlopValidTokens();
        $total_non_vlop_valid_tokens = $this->tokenService->getTotalNonVlopValidTokens();

        return view('profile',[
            'has_platform' => (bool)$request->user()->platform,
            'platform_name' => $request->user()->platform->name ?? '',
            'platform_ids_methods_data' => $platform_ids_methods_data,
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

    /**
     * @param Request $request
     *
     * @return Factory|View|Application
     */
    public function apiIndex(Request $request): Factory|View|Application
    {

        $token_plain_text = null;
        /** @var PersonalAccessToken $token */

        $user = $request->user();

        if (!$user->hasValidApiToken()) {
            /** @var PersonalAccessToken $token */
            $token_plain_text = $user->createToken(User::API_TOKEN_KEY)->plainTextToken;
            if ($user->platform) {
                $user->platform->has_tokens = 1;
                $user->platform->save();
            }
        }

        return view('api', [
            'token_plain_text' => $token_plain_text
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Redirector|Application|RedirectResponse
     */
    public function newToken(Request $request): Redirector|Application|RedirectResponse
    {
        $request->user()->tokens()->delete();
        return redirect(route('profile.api.index'));
    }
}
