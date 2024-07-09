<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Models\Platform;
use App\Models\User;
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
        //$total_valid_tokens = PersonalAccessToken::query()->orWhereNull('expires_at')->orwhere('expires_at', '>=', Carbon::now())->count();
        $total_vlop_valid_tokens = User::join('personal_access_tokens', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->join('platforms', 'platforms.id', '=', 'users.platform_id')
            ->where('platforms.vlop', 1)
            ->whereNot('platforms.name','DSA Team')
            ->whereNull('users.deleted_at')
            ->count('users.id');

        $total_non_vlop_valid_tokens = User::join('personal_access_tokens', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->join('platforms', 'platforms.id', '=', 'users.platform_id')
            ->where('platforms.vlop', 0)

            ->whereNull('users.deleted_at')
            ->count('users.id');

        return view('onboarding.index', [
            'platforms' => $platforms,
            'vlop_count' => $vlop_count,
            'total_vlop_platforms_sending' => $total_vlop_platforms_sending,
            'total_non_vlop_platforms_sending' => $total_non_vlop_platforms_sending,
            'total_vlop_valid_tokens' =>  $total_vlop_valid_tokens,
            'total_non_vlop_valid_tokens' =>  $total_non_vlop_valid_tokens,
        ]);

    }


}
