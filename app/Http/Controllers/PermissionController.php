<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;


class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): Factory|View|Application
    {
        $permissions = Permission::orderBy('name', 'asc')->paginate(20);
        return view('permission.index', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): View|Factory|Application
    {
        $permission = new Permission();
        $options = [];
        return view('permission.create', [
            'permission' => $permission,
            'options' => $options,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return RedirectResponse
     */
    public function store(PermissionStoreRequest $request): RedirectResponse
    {
        $validated = $request->safe()->merge([
        ])->toArray();

        Permission::create(['name' => $validated['name']]);
        return redirect()->route('permission.index')->with('success', 'The permission has been created');
    }

    /**
     * Display the specified resource.
     *
     *
     * @return RedirectResponse
     */
    public function show(Permission $permission): RedirectResponse
    {
        return redirect()->route('permission.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     *
     * @return Application|Factory|View
     */
    public function edit(Permission $permission): View|Factory|Application
    {
        $options = [];
        return view('permission.edit', [
            'permission' => $permission,
            'options' => $options,
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     *
     * @return RedirectResponse
     */
    public function update(PermissionUpdateRequest $request, Permission $permission): RedirectResponse
    {
        $validated = $request->safe()->merge([

        ])->toArray();
        $permission->name = $validated['name'];
        $permission->save();
        return redirect()->route('permission.index')->with('success', 'The permission has been saved');
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @return RedirectResponse
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();
        return redirect()->route('permission.index')->with('success', 'The permission has been deleted');
    }
}
