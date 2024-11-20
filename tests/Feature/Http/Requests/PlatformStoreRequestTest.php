<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\PlatformStoreRequest;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PlatformStoreRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private PlatformStoreRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();

        // Create a test route for our request
        Route::post('test-platform-store', function (PlatformStoreRequest $request) {
            return response()->json($request->validated());
        })->middleware('web')->name('test-platform-store');

        // Create the request
        $this->request = new PlatformStoreRequest();
        $this->request->setContainer($this->app);
        $this->request->setRedirector($this->app->make('redirect'));
        $this->request->setRouteResolver(function () {
            return Route::getRoutes()->getByName('test-platform-store');
        });
    }

    /** @test */
    public function authorized_users_can_create_platform()
    {
        $this->actingAs($this->user);
        
        // Allow the permission
        Gate::define('create platforms', function ($user) {
            return true;
        });

        $response = $this->postJson('test-platform-store', [
            'name' => 'New Platform',
            'vlop' => 1,
            'dsa_common_id' => 'test-id'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthorized_users_cannot_create_platform()
    {
        $this->actingAs($this->user);
        
        // Deny the permission
        Gate::define('create platforms', function ($user) {
            return false;
        });

        $response = $this->postJson('test-platform-store', [
            'name' => 'New Platform',
            'vlop' => 1
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function name_is_required()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        $response = $this->postJson('test-platform-store', [
            'vlop' => 1
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function name_must_be_string()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        $response = $this->postJson('test-platform-store', [
            'name' => 123,
            'vlop' => 1
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function name_must_not_exceed_max_length()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        $response = $this->postJson('test-platform-store', [
            'name' => str_repeat('a', 256),
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

        $response = $this->postJson('test-platform-store', [
            'name' => 'New Platform'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vlop']);
    }

    /** @test */
    public function vlop_must_be_integer()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        $response = $this->postJson('test-platform-store', [
            'name' => 'New Platform',
            'vlop' => 'not-an-integer'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vlop']);
    }

    /** @test */
    public function optional_fields_can_be_null()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        $response = $this->postJson('test-platform-store', [
            'name' => 'New Platform',
            'vlop' => 1,
            'onboarded' => null,
            'has_tokens' => null,
            'has_statements' => null,
            'dsa_common_id' => null
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function dsa_common_id_must_be_unique()
    {
        $this->actingAs($this->user);
        Gate::define('create platforms', fn() => true);

        // Create a platform with a DSA common ID
        Platform::factory()->create(['dsa_common_id' => 'existing-id']);

        $response = $this->postJson('test-platform-store', [
            'name' => 'New Platform',
            'vlop' => 1,
            'dsa_common_id' => 'existing-id'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dsa_common_id']);
    }

    /** @test */
    public function in_method_builds_validation_rule_correctly()
    {
        // Create a request instance
        $request = new PlatformStoreRequest();
        
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

        // Test with numeric values
        $result = $method->invoke($request, [1, 2, 3]);
        $this->assertEquals('in:1,2,3', $result);
    }
}
