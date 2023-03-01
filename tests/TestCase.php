<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function signInAsAdmin($user = null){
        $user = $this->signIn($user);
        PermissionsSeeder::resetRolesAndPermissions();
        $user->assignRole('Admin');
        return $user;
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
