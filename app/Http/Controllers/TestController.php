<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function public(Request $request)
    {
        return view('public');
    }

    public function profile(Request $request)
    {
        dd(auth()->user());
//        dd(auth()->user()->getAttribute('firstName'));
//        dd(auth()->user()->getAttributes());
//        $user = auth()->user(); print_r($user);
//        $user = auth()->user(); print_r($user);
        return view('profile');
    }
}
