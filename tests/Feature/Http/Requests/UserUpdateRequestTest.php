<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $targetUser;

    private UserUpdateRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->user = User::factory()->create();
        $this->targetUser = User::factory()->create();

        // Create a test route for our request
        Route::put('test-user-update/{user}', function (UserUpdateRequest $request, User $user) {
            return response()->json($request->validated());
        })->middleware('web');

        // Create the request
        $this->request = new UserUpdateRequest;
        $this->request->setContainer($this->app);
        $this->request->setRedirector($this->app->make('redirect'));
        $this->request->setRouteResolver(function () {
            return Route::getRoutes()->getByName('test-user-update');
        });
    }

    /** @test */
    public function authorized_users_can_update_users()
    {
        $this->actingAs($this->user);

        // Allow the permission
        Gate::define('create users', function ($user) {
            return true;
        });

        $response = $this->putJson("/test-user-update/{$this->targetUser->id}", [
            'email' => 'new@example.com',
            'platform_id' => 1,
            'roles' => ['admin'],
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthorized_users_cannot_update_users()
    {
        $this->actingAs($this->user);

        // Deny the permission
        Gate::define('create users', function ($user) {
            return false;
        });

        $response = $this->putJson("/test-user-update/{$this->targetUser->id}", [
            'email' => 'new@example.com',
            'platform_id' => 1,
            'roles' => ['admin'],
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function email_must_be_valid()
    {
        $this->actingAs($this->user);
        Gate::define('create users', fn () => true);

        $response = $this->putJson("/test-user-update/{$this->targetUser->id}", [
            'email' => 'not-an-email',
            'roles' => ['admin'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function email_must_be_unique_except_for_current_user()
    {
        $this->actingAs($this->user);
        Gate::define('create users', fn () => true);

        // Create another user with a known email
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        // Try to update target user with existing email
        $response = $this->putJson("/test-user-update/{$this->targetUser->id}", [
            'email' => 'existing@example.com',
            'roles' => ['admin'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Should allow using the same email for the same user
        $response = $this->putJson("/test-user-update/{$existingUser->id}", [
            'email' => 'existing@example.com',
            'roles' => ['admin'],
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function platform_id_must_be_integer_if_provided()
    {
        $this->actingAs($this->user);
        Gate::define('create users', fn () => true);

        $response = $this->putJson("/test-user-update/{$this->targetUser->id}", [
            'email' => 'valid@example.com',
            'platform_id' => 'not-an-integer',
            'roles' => ['admin'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['platform_id']);
    }

    /** @test */
    public function platform_id_can_be_null()
    {
        $this->actingAs($this->user);
        Gate::define('create users', fn () => true);

        $response = $this->putJson("/test-user-update/{$this->targetUser->id}", [
            'email' => 'valid@example.com',
            'platform_id' => null,
            'roles' => ['admin'],
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function roles_must_be_an_array()
    {
        $this->actingAs($this->user);
        Gate::define('create users', fn () => true);

        $response = $this->putJson("/test-user-update/{$this->targetUser->id}", [
            'email' => 'valid@example.com',
            'roles' => 'not-an-array',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles']);
    }

    /** @test */
    public function roles_are_required()
    {
        $this->actingAs($this->user);
        Gate::define('create users', fn () => true);

        $response = $this->putJson("/test-user-update/{$this->targetUser->id}", [
            'email' => 'valid@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles']);
    }
}
