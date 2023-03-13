<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Class ImpersonateController extends Controller {

    public function impersonate(Request $request)
    {
        $validated_data_that_was_sent_in_via_the_form = $request->validate([
            'username' => 'required',
        ]);
        $the_desired_user_to_impersonate = User::firstWhere('eu_login_username', $validated_data_that_was_sent_in_via_the_form['username']);
        if ($the_desired_user_to_impersonate) {
            Auth::user()->setImpersonating($the_desired_user_to_impersonate->id);
        }
        return redirect()->back();
    }

    public function stopImpersonate()
    {
        Auth::user()->stopImpersonating();
        return redirect()->route('home');
    }

}
