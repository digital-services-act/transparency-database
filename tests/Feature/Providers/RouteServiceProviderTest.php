<?php

namespace Tests\Feature\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RouteServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_api_requests_have_second_and_minute_limits(): void
    {
        $user = User::factory()->create();
        $request = Request::create('/api/v1/statement', 'POST');
        $request->setUserResolver(static fn () => $user);

        $limits = RateLimiter::limiter('api')($request);

        $this->assertIsArray($limits);
        $this->assertCount(2, $limits);

        $this->assertLimit($limits[0], 200, 1, 'second:user:'.$user->id);
        $this->assertLimit($limits[1], 12000, 60, 'minute:user:'.$user->id);
    }

    public function test_anonymous_api_requests_keep_the_existing_minute_limit(): void
    {
        $request = Request::create('/api/v1/statement', 'POST', server: [
            'REMOTE_ADDR' => '203.0.113.10',
        ]);

        $limit = RateLimiter::limiter('api')($request);

        $this->assertLimit($limit, 100, 60, '203.0.113.10');
    }

    public function test_multiple_statement_api_requests_have_a_lower_user_limit(): void
    {
        $user = User::factory()->create();
        $request = Request::create('/api/v1/statements', 'POST');
        $request->setUserResolver(static fn () => $user);

        $limits = RateLimiter::limiter('api-multiple')($request);

        $this->assertIsArray($limits);
        $this->assertCount(2, $limits);

        $this->assertLimit($limits[0], 2, 1, 'second:user:'.$user->id);
        $this->assertLimit($limits[1], 120, 60, 'minute:user:'.$user->id);
    }

    public function test_anonymous_multiple_statement_api_requests_keep_the_same_ip_limit(): void
    {
        $request = Request::create('/api/v1/statements', 'POST', server: [
            'REMOTE_ADDR' => '203.0.113.11',
        ]);

        $limit = RateLimiter::limiter('api-multiple')($request);

        $this->assertLimit($limit, 100, 60, '203.0.113.11');
    }

    private function assertLimit(Limit $limit, int $attempts, int $decaySeconds, string $key): void
    {
        $this->assertSame($attempts, $limit->maxAttempts);
        $this->assertSame($decaySeconds, $limit->decaySeconds);
        $this->assertSame($key, $limit->key);
    }
}
