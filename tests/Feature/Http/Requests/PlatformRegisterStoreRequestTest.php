<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\PlatformRegisterStoreRequest;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PlatformRegisterStoreRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $dsaUser;
    private Platform $platform;
    private PlatformRegisterStoreRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a DSA platform
        $dsaPlatform = Platform::factory()->create(['name' => Platform::LABEL_DSA_TEAM]);
        
        // Create users
        $this->user = User::factory()->create(['platform_id' => null]); // Explicitly set no platform
        $this->dsaUser = User::factory()->create(['platform_id' => $dsaPlatform->id]);
        
        // Create a platform
        $this->platform = Platform::factory()->create();

        // Create a test route for our request
        Route::post('test-platform-register', function (PlatformRegisterStoreRequest $request) {
            return response()->json($request->validated());
        })->middleware('web')->name('test-platform-register'); // Add route name

        // Create the request
        $this->request = new PlatformRegisterStoreRequest();
        $this->request->setContainer($this->app);
        $this->request->setRedirector($this->app->make('redirect'));
        $this->request->setRouteResolver(function () {
            return Route::getRoutes()->getByName('test-platform-register');
        });
    }

    /** @test */
    public function users_without_platform_can_register()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('test-platform-register', [
            'name' => 'Test Platform',
            'url' => 'https://test-platform.com'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function dsa_team_users_can_register_platforms()
    {
        $this->actingAs($this->dsaUser);

        $response = $this->postJson('test-platform-register', [
            'name' => 'Test Platform',
            'url' => 'https://test-platform.com'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function users_with_platform_cannot_register()
    {
        // Assign a platform to the user
        $this->user->platform_id = $this->platform->id;
        $this->user->save();
        
        $this->actingAs($this->user);

        $response = $this->postJson('test-platform-register', [
            'name' => 'Test Platform',
            'url' => 'https://test-platform.com'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function name_is_required()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('test-platform-register', [
            'url' => 'https://test-platform.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function name_must_be_string()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('test-platform-register', [
            'name' => 123,
            'url' => 'https://test-platform.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function name_must_not_exceed_255_characters()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('test-platform-register', [
            'name' => str_repeat('a', 256),
            'url' => 'https://test-platform.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function url_is_required()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('test-platform-register', [
            'name' => 'Test Platform'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function url_must_be_valid()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('test-platform-register', [
            'name' => 'Test Platform',
            'url' => 'not-a-url'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function url_must_not_exceed_255_characters()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('test-platform-register', [
            'name' => 'Test Platform',
            'url' => 'https://' . str_repeat('a', 246) . '.com' // 255+ characters
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }
}
