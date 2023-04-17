<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        return view('login.login');
    }

    public function submit(Request $request)
    {
        $validated_data_that_was_sent_in_via_the_form = $request->validate([
            'username' => 'required',
        ]);
//dd($validated_data_that_was_sent_in_via_the_form['username']);
        $the_desired_user_to_impersonate = User::firstWhere('name', 'LIKE', '%'.$validated_data_that_was_sent_in_via_the_form['username'].'%');

        if (!$the_desired_user_to_impersonate) return back()->withErrors('Wrong Credentials');
        if ($the_desired_user_to_impersonate) {
            Auth::user()->setImpersonating($the_desired_user_to_impersonate->id);
        }
        return redirect(route('dashboard'));


    }
}
