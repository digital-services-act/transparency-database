<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlatformRegisterStoreRequest;
use App\Http\Requests\PlatformStoreRequest;
use App\Http\Requests\PlatformUpdateRequest;
use App\Models\Platform;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


class PlatformController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function index(Request $request): View|Factory|Application
    {
        $platforms = Platform::query();
        if ($request->get('s')) {
            $platforms = Platform::where('name', 'like', '%' . $request->get('s') . '%');
        }
        $platforms = $platforms->paginate(50)->withQueryString();

        return view('platform.index', [
            'platforms' => $platforms
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): View|Factory|Application
    {
        $platform = new Platform();
        $options = $this->prepareOptions();
        return view('platform.create', [
            'platform' => $platform,
            'options' => $options
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PlatformStoreRequest $request
     *
     * @return RedirectResponse
     */
    public function store(PlatformStoreRequest $request): RedirectResponse
    {
        $validated = $request->safe()->merge([

        ])->toArray();

        if ($validated['name'] == Platform::LABEL_DSA_TEAM)
        {
            return redirect()->route('platform.index')->with('error', 'You can not create a platform with the name "'.Platform::LABEL_DSA_TEAM.'"');
        }

        /** @var Platform $platform */
        $platform = Platform::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
        ]);
        return redirect()->route('platform.index')->with('success', 'The platform has been created');
    }

    /**
     * Display the specified resource.
     *
     * @param Platform $platform
     *
     * @return RedirectResponse
     */
    public function show(Platform $platform): RedirectResponse
    {
        return redirect()->route('platform.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Platform $platform
     *
     * @return Application|Factory|View
     */
    public function edit(Platform $platform): View|Factory|Application
    {
        $options = $this->prepareOptions();
        return view('platform.edit', [
            'platform' => $platform,
            'options' => $options
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param PlatformUpdateRequest $request
     * @param Platform $platform
     *
     * @return RedirectResponse
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
        $platform->url = $validated['url'];
        $platform->save();
        return redirect()->route('platform.index')->with('success', 'The platform has been saved');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Platform  $platform
     *
     * @return RedirectResponse
     */
    public function destroy(Platform $platform): RedirectResponse
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

    /**
     * @param Request $request
     *
     * @return Application|Factory|View|\Illuminate\Foundation\Application|RedirectResponse
     */
    public function platformRegister(Request $request)
    {
        if ($request->user()->platform && $request->user()->platform->name !== Platform::LABEL_DSA_TEAM) {
            return redirect()->route('dashboard')->with('error', 'Your account is currently linked to a platform and therefor you may not register a new platform.');
        }

        $options = $this->prepareOptions();
        return view('platform.register', compact(
            'options'
        ));
    }

    public function platformRegisterStore(PlatformRegisterStoreRequest $request): RedirectResponse
    {
        $validated = $request->safe()->merge([

        ])->toArray();

        return redirect()->route('dashboard')->with('success', 'The platform has been registered, representatives will be in contact with you shortly.');
    }

    private function prepareOptions(): array
    {
        return [];
    }


}
