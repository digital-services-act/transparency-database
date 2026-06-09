<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OnboardingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create role
        $onboardingRole = Role::create(['name' => 'Onboarding']);

        // Create permissions
        $createPlatformPermission = Permission::create(['name' => 'create platforms']);
        $viewPlatformPermission = Permission::create(['name' => 'view platforms']);
        $createUserPermission = Permission::create(['name' => 'create users']);
        $viewUserPermission = Permission::create(['name' => 'view users']);

        // Assign permissions to role
        $onboardingRole->syncPermissions(['view dashboard', 'create statements', $viewPlatformPermission, $createUserPermission, $viewUserPermission, $createPlatformPermission]);

    }
}
