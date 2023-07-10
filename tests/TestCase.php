<?php

namespace Tests;

use App\Models\Platform;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function signInAsAdmin($user = null) {
        $user = $this->signIn($user);
        PermissionsSeeder::resetRolesAndPermissions();
        $user->assignRole('Admin');
        $this->assignPlatform($user);
        return $user;
    }

    protected function assignPlatform($user, $platform = null)
    {
        $user->platform_id = $platform->id ?? Platform::all()->random()->first()->id;
        $user->save();
    }

    protected function signIn($user = null)
    {
        $user = $user ?: User::create([
            'name' => "Test User",
            'email' => "testuser@testing.org",
            'eu_login_username' => "testuser",
            'password' => 'testpassword'
        ]);
        $this->actingAs($user);

        return $user;
    }

}
