<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlatformStoreRequest;
use App\Http\Requests\PlatformUpdateRequest;
use App\Models\Platform;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PlatformController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|Factory|Application
    {
        $platforms = Platform::query();
        if ($request->get('s')) {
            $platforms = Platform::where('name', 'like', '%'.$request->get('s').'%');
        }

        $platforms->orderBy('name');
        $platforms = $platforms->paginate(50)->withQueryString();

        return view('platform.index', [
            'platforms' => $platforms,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|Factory|Application
    {
        $platform = new Platform;
        $options = $this->prepareOptions();

        return view('platform.create', [
            'platform' => $platform,
            'options' => $options,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlatformStoreRequest $request): RedirectResponse
    {
        $validated = $request->safe()->merge([

        ])->toArray();

        if ($validated['name'] == Platform::LABEL_DSA_TEAM) {
            return redirect()->route('platform.index')->with('error', 'You can not create a platform with the name "'.Platform::LABEL_DSA_TEAM.'"');
        }

        /** @var Platform $platform */
        $platform = Platform::create([
            'name' => $validated['name'],
            'dsa_common_id' => $validated['dsa_common_id'] ?? null,
            'vlop' => $validated['vlop'],
            'onboarded' => $validated['onboarded'] ?? 0,
            'has_tokens' => 0,
            'has_statements' => 0,
        ]);

        return redirect()->route('platform.index')->with('success', 'The platform has been created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Platform $platform): RedirectResponse
    {
        return redirect()->route('platform.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Platform $platform): View|Factory|Application
    {
        $options = $this->prepareOptions();
        $request = request();
        Session::remove('returnto');
        if ($request && $request->query('returnto')) {
            Session::put('returnto', $request->query('returnto'));
        }

        return view('platform.edit', [
            'platform' => $platform,
            'options' => $options,
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PlatformUpdateRequest $request, Platform $platform): RedirectResponse
    {
        $dsaPlatform = Platform::getDsaPlatform();

        if ($platform->id == $dsaPlatform->id) {
            return redirect()->route('platform.index')->with('error', 'You may never delete/change the DSA Platform');
        }

        $validated = $request->safe()->merge([

        ])->toArray();
        $platform->name = $validated['name'];

        $platform->dsa_common_id = $validated['dsa_common_id'];

        $platform->vlop = $validated['vlop'];
        $platform->onboarded = $validated['onboarded'] ?? $platform->onboarded;
        $platform->has_tokens = $validated['has_tokens'] ?? $platform->has_tokens;
        $platform->has_statements = $validated['has_statements'] ?? $platform->has_statements;
        $platform->save();

        $returnto = Session::get('returnto');
        if ($returnto) {
            Session::remove('returnto');

            return redirect()->to($returnto)->with('success', 'The platform has been saved');
        }

        return redirect()->route('platform.index')->with('success', 'The platform has been saved');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Platform $platform): RedirectResponse
    {
        $dsaPlatform = Platform::getDsaPlatform();

        if ($platform->id == $dsaPlatform->id) {
            return redirect()->route('platform.index')->with('error', 'You may never delete/change the DSA Platform');
        }

        // Change all statements to DSA
        $platform->statements()->update(['platform_id' => $dsaPlatform->id]);

        // Delete all the users
        $platform->users()->delete();

        // delete the platform.
        $platform->delete();

        // Carry on
        return redirect()->route('platform.index')->with('success', 'The platform has been deleted');
    }

    private function prepareOptions(): array
    {
        $vlops = [
            [
                'label' => 'Yes',
                'value' => 1,
            ],
            [
                'label' => 'No',
                'value' => 0,
            ],
        ];
        $onboardeds = [
            [
                'label' => 'Yes',
                'value' => 1,
            ],
            [
                'label' => 'No',
                'value' => 0,
            ],
        ];
        $has_tokens = [
            [
                'label' => 'Yes',
                'value' => 1,
            ],
            [
                'label' => 'No',
                'value' => 0,
            ],
        ];
        $has_statements = [
            [
                'label' => 'Yes',
                'value' => 1,
            ],
            [
                'label' => 'No',
                'value' => 0,
            ],
        ];

        return [
            'vlops' => $vlops,
            'onboardeds' => $onboardeds,
            'has_tokens' => $has_tokens,
            'has_statements' => $has_statements,
        ];
    }
}
