<?php

namespace Tests\Feature\Console\Commands\Setup;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GiveRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_assigns_role_to_user_successfully(): void
    {
        // Create a test role and user
        $role = Role::create(['name' => 'test-role']);
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Run the command
        $this->artisan('give-role', ['role' => 'test-role', 'email' => 'test@example.com'])
            ->expectsOutput('The role was given to the user.')
            ->assertExitCode(0);

        // Verify the user has the role
        $this->assertTrue($user->fresh()->hasRole('test-role'));
    }

    /**
     * @test
     */
    public function it_shows_error_when_role_not_found(): void
    {
        // Create a test user but no role
        User::factory()->create(['email' => 'test@example.com']);

        // Run the command with non-existent role
        $this->artisan('give-role', ['role' => 'non-existent-role', 'email' => 'test@example.com'])
            ->expectsOutput('The role was not found.')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_shows_error_when_user_not_found(): void
    {
        // Create a test role but no user
        Role::create(['name' => 'test-role']);

        // Run the command with non-existent user
        $this->artisan('give-role', ['role' => 'test-role', 'email' => 'nonexistent@example.com'])
            ->expectsOutput('The user was not found.')
            ->assertExitCode(0);
    }
}
