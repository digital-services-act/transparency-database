<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\DayArchiveQueryService;
use App\Services\DayArchiveService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{


    public function index(Request $request)
    {

        $platforms = Platform::nonVlops()->with('users')->orderBy('name')->get();

        return view('onboarding.index', [
            'platforms' => $platforms
        ]);

    }


}
