<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Http\Middleware\LimitConcurrentMultiStatementRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class StatementMultipleConcurrencyTestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(LimitConcurrentMultiStatementRequests::class);
        Sleep::fake();
    }

    public function test_it_holds_the_request_for_the_requested_seconds(): void
    {
        $user = $this->signInAsAdmin();

        $response = $this->postJson(route('api.v1.statements.concurrency-test'), ['seconds' => 3]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'funnel_key' => 'multi-api-concurrency:{platform:'.$user->platform_id.'}',
                'max_concurrent_requests' => LimitConcurrentMultiStatementRequests::MAX_CONCURRENT_REQUESTS_PER_PLATFORM,
                'slept_seconds' => 3,
            ]);
        Sleep::assertSequence([Sleep::for(3)->seconds()]);
    }

    public function test_it_defaults_and_caps_the_seconds(): void
    {
        $this->signInAsAdmin();

        $this->postJson(route('api.v1.statements.concurrency-test'))
            ->assertJson(['slept_seconds' => 5]);
        $this->postJson(route('api.v1.statements.concurrency-test'), ['seconds' => 999])
            ->assertJson(['slept_seconds' => 20]);
        $this->postJson(route('api.v1.statements.concurrency-test'), ['seconds' => -4])
            ->assertJson(['slept_seconds' => 0]);
    }

    public function test_it_requires_permission_to_create_statements(): void
    {
        $this->signIn();

        $this->postJson(route('api.v1.statements.concurrency-test'))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_it_uses_the_same_limits_as_the_multi_statement_endpoint(): void
    {
        $route = app('router')->getRoutes()->getByName('api.v1.statements.concurrency-test');

        $this->assertContains('throttle:api-multiple', $route->gatherMiddleware());
        $this->assertContains('multi.concurrency', $route->gatherMiddleware());
    }
}
