<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RedirectIfAuthenticatedTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = false;

    private RedirectIfAuthenticated $middleware;

    private Request $request;

    private string $baseUrl;

    #[\Override]
    protected function setUpFullySeededDatabase($statement_count = 10): void
    {
        // Do nothing - we don't need any seeding for this test
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Configure authentication for testing
        config(['auth.guards.web.driver' => 'session']);
        config(['auth.defaults.guard' => 'web']);
        config(['auth.providers.users.model' => \App\Models\User::class]);

        $this->middleware = new RedirectIfAuthenticated;
        $this->request = Request::create('/login', 'GET');
        $this->baseUrl = config('app.url');
    }

    /** @test */
    public function it_allows_guest_to_proceed()
    {
        // Ensure no user is logged in
        Auth::logout();

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals(204, $response->getStatusCode());
    }

    /** @test */
    public function it_redirects_authenticated_user_to_home()
    {
        // Create and login a user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'platform_id' => null,
        ]);

        Auth::login($user);
        $this->assertTrue(Auth::check());

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals($this->baseUrl, $response->getTargetUrl());
    }

    /** @test */
    public function it_handles_multiple_auth_guards()
    {
        // Test with default guard when no user is authenticated
        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        }, 'web', 'api');

        $this->assertEquals(204, $response->getStatusCode());

        // Test with default guard when user is authenticated
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
            'platform_id' => null,
        ]);

        Auth::login($user);
        $this->assertTrue(Auth::check());

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        }, 'web', 'api');

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals($this->baseUrl, $response->getTargetUrl());
    }

    /** @test */
    public function it_handles_empty_guards_array()
    {
        // Test with no guards specified (should use default guard)
        Auth::logout();

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertEquals(204, $response->getStatusCode());

        // Login user and test again
        $user = User::create([
            'name' => 'Test User 3',
            'email' => 'test3@example.com',
            'password' => bcrypt('password'),
            'platform_id' => null,
        ]);

        Auth::login($user);
        $this->assertTrue(Auth::check());

        $response = $this->middleware->handle($this->request, function ($request) {
            return response()->noContent();
        });

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertEquals($this->baseUrl, $response->getTargetUrl());
    }
}
