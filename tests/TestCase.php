<?php

namespace Tests;

use App\Models\Platform;
use App\Models\User;
use Database\Seeders\OnboardingPermissionsSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\PlatformSeeder;
use Database\Seeders\StatementSeeder;
use Database\Seeders\SupportPermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFullySeededDatabase();
    }

    use CreatesApplication;


    protected function signInAsAdmin($user = null) {
        $user = $this->signIn($user);
        $user->assignRole('Admin');

        $dsa_platform = Platform::getDsaPlatform();
        $this->assignPlatform($user, $dsa_platform);
        return $user;
    }

    protected function signInAsOnboarding($user = null) {
        $user = $this->signIn($user);
        $user->assignRole('Onboarding');

        $dsa_platform = Platform::getDsaPlatform();
        $this->assignPlatform($user, $dsa_platform);
        return $user;
    }

    protected function signInAsSupport($user = null) {
        $user = $this->signIn($user);
        $user->assignRole('Support');

        $dsa_platform = Platform::getDsaPlatform();
        $this->assignPlatform($user, $dsa_platform);
        return $user;
    }

    protected function signInAsContributor($user = null) {
        $user = $this->signIn($user);
        $user->assignRole('Contributor');

        $non_dsa_platform = Platform::nonDsa()->first();
        $this->assignPlatform($user, $non_dsa_platform);
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
            'password' => 'testpassword'
        ]);
        $this->actingAs($user);

        return $user;
    }

    protected function setUpFullySeededDatabase($statement_count = 10): void
    {
        PlatformSeeder::resetPlatforms();
        UserSeeder::resetUsers();
        PermissionsSeeder::resetRolesAndPermissions();
        $this->seed(OnboardingPermissionsSeeder::class);
        $this->seed(SupportPermissionsSeeder::class);
        StatementSeeder::resetStatements($statement_count);
    }

}
