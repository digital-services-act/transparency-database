<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{

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
//        $users = User::all();
//        /** @var User $user */
//        foreach ($users as $user)
//        {
//            $user->roles()->detach();
//        }

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
            'view logs',
            'view reports',
            'create statements',
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


        $contributor->givePermissionTo('view statements');
        $contributor->givePermissionTo('view dashboard');
        $contributor->givePermissionTo('view reports');
        $contributor->givePermissionTo('create statements');

        $admin_emails = config('dsa.ADMIN_EMAILS');
        $admin_emails = explode(",", $admin_emails);
        foreach ($admin_emails as $admin_email)
        {
            if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                $admin = User::where('email', $admin_email)->first();
                if ($admin) {
                    $admin->assignRole('Admin');
                }
            }
        }
    }
}
