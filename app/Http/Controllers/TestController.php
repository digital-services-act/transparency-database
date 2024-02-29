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
        return view('profile');
    }
}
