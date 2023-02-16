<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Http\Traits\PlatformsTrait;

class PermissionsSeeder extends Seeder
{

use PlatformsTrait;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        self::resetRolesAndPermissions();

    }

    public static function resetRolesAndPermissions()
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
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $platform_role
     * @param string $platform_name
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Factories\HasFactory|\Illuminate\Database\Eloquent\Model|mixed
     */
    private function createPlatform(\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $platform_role, $platform_name): mixed
    {
        $user = User::factory()->state([
            'name' => $platform_name,
            'email' => "fake-user@" . strtolower($platform_name) . ".com",
            'eu_login_username' => strtolower($platform_name) . "@" .  strtolower($platform_name) . ".com",
        ])->create();
        $user->assignRole($platform_role);
        return $user;
    }
}
