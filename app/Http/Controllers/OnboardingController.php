<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        $platforms = Platform::all()->sortBy('name');

        return view('onboarding.index', compact('platforms'));
    }

    public function create(Request $request)
    {
        return view('onboarding.create');
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|exists:App\Models\Platform,id',
        ]);

        $platform = Platform::firstWhere('id', $validated['platform']);



        dd('join the platform ('.$platform->name.') option is coming soon');
    }
}
