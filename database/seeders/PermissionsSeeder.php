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

        // create permissions
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'create tokens']);
        Permission::create(['name' => 'manage own token']);
        Permission::create(['name' => 'generate reports']);

        // create roles and assign existing permissions
        $platform_role = Role::create(['name' => 'platform']);
        $platform_role->givePermissionTo('view dashboard');
        $platform_role->givePermissionTo('manage own token');

        $ec_role = Role::create(['name' => 'european commission']);
        $ec_role->givePermissionTo('view dashboard');
        $ec_role->givePermissionTo('create tokens');

        $research_role = Role::create(['name' => 'researcher']);
        $research_role->givePermissionTo('view dashboard');
        $research_role->givePermissionTo('generate reports');


        // create demo platforms
        foreach ($this->getPlatforms() as $platform_name) {
            $this->createPlatform($platform_role, $platform_name);
        }

        $user = \App\Models\User::factory()->create([
            'name' => 'COM User',
            'email' => 'user@ec.europa.eu',
            'eu_login_username' => 'com-user'
        ]);
        $user->assignRole($ec_role);


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
