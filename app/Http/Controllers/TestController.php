<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TestController extends Controller
{

    public function token(Request $request)
    {
        $token = $request->user()->createToken('test-token');

        return ['token' => $token->plainTextToken];
    }

    public function resetRolesAndPermissions(Request $request)
    {

        $users = User::all();
        /** @var User $user */
        foreach ($users as $user)
        {
            $user->roles()->detach();
        }

        Role::query()->delete();
        Permission::query()->delete();

        $admin = Role::create([
            'name' => 'Admin'
        ]);

        $user = Role::create([
            'name' => 'User'
        ]);

        $contributor = Role::create([
            'name' => 'Contributor'
        ]);

        $permissions = [
            'administrate',
            'create statements',
            'generate reports',
            'impersonate',
            'view dashboard',
            'view statements'
        ];

        foreach ($permissions as $permission_name)
        {
            $permission = Permission::create(['name' => $permission_name]);
            $admin->givePermissionTo($permission);
        }

        $user->givePermissionTo('view statements');
        $user->givePermissionTo('view dashboard');
        $user->givePermissionTo('view dashboard');

        $contributor->givePermissionTo('view statements');
        $contributor->givePermissionTo('view dashboard');
        $contributor->givePermissionTo('view dashboard');
        $contributor->givePermissionTo('generate reports');
        $contributor->givePermissionTo('create statements');

        $users = User::all();
        /** @var User $user */
        foreach ($users as $user)
        {
            $user->assignRole('Admin');
        }

        return "DONE";

    }

//    public function mount(Request $request)
//    {
//        $url = $request->input('url');
//        $result = scandir($url);
//
//        dd($result);
//    }
}
