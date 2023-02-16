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

class DashboardController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Factory|View|Application
     */
    public function dashboard(Request $request): Factory|View|Application
    {
        $token_plain_text = null;
        /** @var PersonalAccessToken $token */
        $token = $request->user()->tokens()->where('name', User::API_TOKEN_KEY)->get()->last();
        if (!$token) {
            /** @var PersonalAccessToken $token */
            $token_plain_text = $request->user()->createToken(User::API_TOKEN_KEY)->plainTextToken;
        }

        return view('dashboard', [
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
        return redirect(route('dashboard'));
    }
}
