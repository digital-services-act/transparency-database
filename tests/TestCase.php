<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


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
