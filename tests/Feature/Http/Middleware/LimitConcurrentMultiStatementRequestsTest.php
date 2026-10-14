<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\LimitConcurrentMultiStatementRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Limiters\ConcurrencyLimiterBuilder;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class LimitConcurrentMultiStatementRequestsTest extends TestCase
{
    #[\Override]
    protected function setUpFullySeededDatabase($statement_count = 10): void
    {
        // Do nothing - we don't need any seeding for this test
    }

    public function test_request_passes_and_releases_its_slot_when_a_slot_is_free(): void
    {
        $request = $this->requestForUser(7, 3);
        $key = 'multi-api-concurrency:{platform:3}';

        $connection = $this->fakeRedisConnection($key);
        $connection->shouldReceive('eval')
            ->once()
            ->withArgs(static fn (...$args): bool => $args[1] === 2
                && $args[2] === $key.'1'
                && $args[3] === $key.'2'
                && $args[5] === LimitConcurrentMultiStatementRequests::SLOT_RELEASE_AFTER_SECONDS)
            ->andReturn($key.'1');
        $connection->shouldReceive('eval')
            ->once()
            ->withArgs(static fn (...$args): bool => $args[1] === 1 && $args[2] === $key.'1')
            ->andReturn(1);

        $response = (new LimitConcurrentMultiStatementRequests)->handle($request, static fn () => response('handled'));

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('handled', $response->getContent());
    }

    public function test_request_is_rejected_when_the_platform_has_no_free_slot(): void
    {
        $request = $this->requestForUser(7, 3);

        $connection = $this->fakeRedisConnection('multi-api-concurrency:{platform:3}');
        $connection->shouldReceive('eval')->once()->andReturn(false);

        $nextWasCalled = false;
        $response = (new LimitConcurrentMultiStatementRequests)->handle($request, static function () use (&$nextWasCalled) {
            $nextWasCalled = true;

            return response('handled');
        });

        $this->assertFalse($nextWasCalled);
        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        $this->assertSame('1', $response->headers->get('Retry-After'));
    }

    public function test_funnel_key_is_per_platform(): void
    {
        $this->assertSame(
            LimitConcurrentMultiStatementRequests::funnelKey($this->requestForUser(7, 3)),
            LimitConcurrentMultiStatementRequests::funnelKey($this->requestForUser(8, 3)),
        );
        $this->assertSame('multi-api-concurrency:{platform:3}', LimitConcurrentMultiStatementRequests::funnelKey($this->requestForUser(7, 3)));
    }

    public function test_funnel_key_falls_back_to_the_user_without_a_platform(): void
    {
        $this->assertSame('multi-api-concurrency:{user:7}', LimitConcurrentMultiStatementRequests::funnelKey($this->requestForUser(7, null)));
    }

    public function test_multi_statement_route_uses_the_concurrency_limit(): void
    {
        $middleware = Route::getRoutes()->getByName('api.v1.statements.store')->gatherMiddleware();

        $this->assertContains('multi.concurrency', $middleware);
    }

    private function requestForUser(int $user_id, ?int $platform_id): Request
    {
        $user = (new User)->forceFill(['id' => $user_id, 'platform_id' => $platform_id]);

        $request = Request::create('/api/v1/statements', 'POST');
        $request->setUserResolver(static fn () => $user);

        return $request;
    }

    private function fakeRedisConnection(string $expected_key): MockInterface
    {
        $connection = Mockery::mock(Connection::class);

        Redis::shouldReceive('funnel')
            ->once()
            ->with($expected_key)
            ->andReturnUsing(static fn (string $name) => new ConcurrencyLimiterBuilder($connection, $name));

        return $connection;
    }
}
