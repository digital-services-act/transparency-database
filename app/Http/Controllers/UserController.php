<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Platform;
use App\Models\Statement;
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
    public function index(Request $request): View|Factory|Application
    {
        $users = User::query();
        $s = $request->get('s');
        if ($s) {
            $users->where('name', 'like', '%' . $s . '%')->orWhere('email', 'like', '%' . $s . '%')->orWhereHas('platform', function($inner_query) use ($s){
                $inner_query->where('name', 'like', '%' . $s . '%');
            });
        }
        $users = $users->paginate(50)->withQueryString();

        return view('user.index', [
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): View|Factory|Application
    {
        $user = new User();
        $options = $this->prepareOptions();

        return view('user.create', [
            'user' => $user,
            'options' => $options,
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
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'platform_id' => $validated['platform_id'],
            'eu_login_username' => $validated['email']
        ]);
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
        $options = $this->prepareOptions();
        return view('user.edit', [
            'user' => $user,
            'options' => $options,
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
        $user->email = $validated['email'];
//        $user->eu_login_username = $user->eu_login_username;
        $user->platform_id = $validated['platform_id'];
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
    public function destroy(User $user): RedirectResponse
    {
        // Delete statements that this guy made.
        // $user->statements()->delete();
        $user->delete();
        return redirect()->route('user.index')->with('success', 'The user has been deleted');
    }

    private function prepareOptions()
    {
        $platforms = Platform::query()->orderBy('name', 'ASC')->get()->map(function($platform){
            return [
                'value' => $platform->id,
                'label' => $platform->name
            ];
        })->toArray();
        array_unshift($platforms, ['value' => '', 'label' => 'Choose a platform']);
        $roles = Role::orderBy('name')->get();

        return compact('platforms', 'roles');
    }
}
