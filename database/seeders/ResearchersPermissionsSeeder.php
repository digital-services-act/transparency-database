<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ResearchersPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create role
        $researcherRole = Role::create(['name' => 'Researcher']);

        // Create permissions
        $researchAPIPermission = Permission::create(['name' => 'research API']);

        // Assign permissions to role
        $researcherRole->syncPermissions([$researchAPIPermission]);

    }
}
