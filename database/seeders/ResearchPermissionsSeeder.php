<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ResearchPermissionsSeeder extends Seeder
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
        $generateAPIKeyPermission = Permission::create(['name' => 'generate API Key']);

        // Assign permissions to role
        $researcherRole->syncPermissions([$researchAPIPermission, $generateAPIKeyPermission]);

    }
}
