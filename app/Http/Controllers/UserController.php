<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $users = User::paginate(10);
        if ($request->get('s')) {
            $users = User::where('name', 'like', '%' . $request->get('s') . '%')->orWhere('email', 'like', '%' . $request->get('s') . '%')->paginate(10)->withQueryString();
        }
        return view('user.index', [
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $user = new User();
        $options = [];
        $roles = Role::all();
        return view('user.create', [
            'user' => $user,
            'options' => $options,
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserStoreRequest $request
     *
     * @return RedirectResponse
     */
    public function store(UserStoreRequest $request)
    {
        $validated = $request->safe()->merge([

        ])->toArray();

        /** @var User $user */
        $user = User::create(['name' => $validated['name']]);
        foreach ($validated['roles'] as $id) {
            $user->roles()->attach($id);
        }
        return redirect()->route('user.index')->with('success', 'The user has been created');
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function show(User $user)
    {
        return redirect()->route('user.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     *
     * @return Application|Factory|View
     */
    public function edit(User $user)
    {
        $options = [];
        $roles = Role::orderBy('name')->get();
        return view('user.edit', [
            'user' => $user,
            'options' => $options,
            'roles' => $roles
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserUpdateRequest $request
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $validated = $request->safe()->merge([

        ])->toArray();
        $user->name = $validated['name'];
        $user->save();
        $user->roles()->detach();
        foreach ($validated['roles'] as $id) {
            $user->roles()->attach($id);
        }
        return redirect()->route('user.index')->with('success', 'The user has been saved');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     *
     * @return RedirectResponse
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('user.index')->with('success', 'The user has been deleted');
    }
}
