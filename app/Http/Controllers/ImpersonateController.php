<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

Class ImpersonateController extends Controller {

    public function impersonate(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required',
        ]);

        if(!App::environment('production') && !App::environment('acceptance')) {
            $user = User::firstWhere('eu_login_username', $validated['username']);

            Auth::user()->setImpersonating($user->id);

            return redirect()->back();
        }
    }

    public function stopImpersonate()
    {
        if(!App::environment('production') && !App::environment('acceptance')) {
            Auth::user()->stopImpersonating();

            return redirect()->route('root');
        }
    }

}
