<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlatformStoreRequest;
use App\Http\Requests\PlatformUpdateRequest;
use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
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

        /** @var Platform $platform */
        $platform = Platform::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'type' => $validated['type'],
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
        $validated = $request->safe()->merge([

        ])->toArray();
        $platform->name = $validated['name'];
        $platform->url = $validated['url'];
        $platform->type = $validated['type'];
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
        $platform->statements()->delete();
        $platform->users()->delete();
        // delete the platform.
        $platform->delete();
        // Carry on
        return redirect()->route('platform.index')->with('success', 'The platform has been deleted');
    }

    private function prepareOptions(): array
    {
        $platform_types = $this->mapForSelectWithKeys(Platform::PLATFORM_TYPES);

        return compact('platform_types');
    }

    /**
     * @param $array
     *
     * @return array
     */
    private function mapForSelectWithoutKeys($array): array
    {
        return array_map(function ($value) {
            return ['value' => $value, 'label' => $value];
        }, $array);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function mapForSelectWithKeys(array $array): array
    {
        return array_map(function ($key, $value) {
            return ['value' => $key, 'label' => $value];
        }, array_keys($array), array_values($array));
    }
}
