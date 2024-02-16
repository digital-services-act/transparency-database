<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SupportPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create role
        $supportRole = Role::create(['name' => 'Support']);

        // Assign permissions to role
        $supportRole->givePermissionTo('view logs');
        $supportRole->givePermissionTo('create platforms');
        $supportRole->givePermissionTo('view platforms');
        $supportRole->givePermissionTo('create users');
        $supportRole->givePermissionTo('view users');
    }
}
