<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\PlatformUpdateRequest;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PlatformUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Platform $platform;
    private PlatformUpdateRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->platform = Platform::factory()->create();

        // Create a test route for our request
        Route::put('test-platform-update/{platform}', function (PlatformUpdateRequest $request, Platform $platform) {
            return response()->json($request->validated());
        })->middleware('web')->name('test-platform-update');

        // Create the request
        $this->request = new PlatformUpdateRequest();
        $this->request->setContainer($this->app);
        $this->request->setRedirector($this->app->make('redirect'));
        $this->request->setRouteResolver(function () {
            return Route::getRoutes()->getByName('test-platform-update');
        });
    }

    /** @test */
    public function authorized_users_can_update_platform()
    {
        $this->actingAs($this->user);
        
        // Allow the permission
        Gate::define('create platforms', function ($user) {
            return true;
        });

        $response = $this->putJson("test-platform-update/{$this->platform->id}", [
            'name' => 'Updated Platform',
            'vlop' => 1,
            'dsa_common_id' => 'test-id'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function users_with_view_permission_can_update_platform()
    {
        $this->actingAs($this->user);
        
        // Allow only view permission
        Gate::define('view platforms', function ($user) {
            return true;
        });

        $response = $this->putJson("test-platform-update/{$this->platform->id}", [
            'name' => 'Updated Platform',
            'vlop' => 1,
            'dsa_common_id' => 'test-id'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthorized_users_cannot_update_platform()
    {
        $this->actingAs($this->user);
        
        // Deny all permissions
        Gate::define('create platforms', function ($user) {
            return false;
        });
        Gate::define('view platforms', function ($user) {
            return false;
        });

        $response = $this->putJson("test-platform-update/{$this->platform->id}", [
            'name' => 'Updated Platform',
            'vlop' => 1
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function name_is_required()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        $response = $this->putJson("test-platform-update/{$this->platform->id}", [
            'vlop' => 1
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function vlop_is_required()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        $response = $this->putJson("test-platform-update/{$this->platform->id}", [
            'name' => 'Updated Platform'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vlop']);
    }

    /** @test */
    public function dsa_common_id_must_be_unique()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        // Create another platform with a DSA common ID
        $otherPlatform = Platform::factory()->create(['dsa_common_id' => 'existing-id']);

        $response = $this->putJson("test-platform-update/{$this->platform->id}", [
            'name' => 'Updated Platform',
            'vlop' => 1,
            'dsa_common_id' => 'existing-id'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dsa_common_id']);
    }

    /** @test */
    public function dsa_common_id_can_remain_same()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        // Set a DSA common ID for our platform
        $this->platform->dsa_common_id = 'my-id';
        $this->platform->save();

        $response = $this->putJson("test-platform-update/{$this->platform->id}", [
            'name' => 'Updated Platform',
            'vlop' => 1,
            'dsa_common_id' => 'my-id'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function in_method_builds_validation_rule_correctly()
    {
        // Create a request instance
        $request = new PlatformUpdateRequest();
        
        // Use reflection to access private method
        $reflectionClass = new \ReflectionClass($request);
        $method = $reflectionClass->getMethod('in');
        $method->setAccessible(true);

        // Test with simple array
        $result = $method->invoke($request, ['a', 'b', 'c']);
        $this->assertEquals('in:a,b,c', $result);

        // Test with empty array
        $result = $method->invoke($request, []);
        $this->assertEquals('in:', $result);

        // Test with array containing special characters
        $result = $method->invoke($request, ['a,b', 'c:d', 'e;f']);
        $this->assertEquals('in:a,b,c:d,e;f', $result);
    }
}
