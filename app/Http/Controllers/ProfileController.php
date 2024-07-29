<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Laravel\Sanctum\PersonalAccessToken;

class ProfileController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Factory|View|Application
     */
    public function profile(Request $request): Factory|View|Application
    {
        return view('profile',[
            'has_platform' => (bool)$request->user()->platform,
            'platform_name' => $request->user()->platform->name ?? ''
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
