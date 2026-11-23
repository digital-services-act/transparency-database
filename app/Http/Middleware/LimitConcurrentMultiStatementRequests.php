<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

/**
 * Caps how many multi statement requests a platform may have in flight at once,
 * so a single platform cannot exhaust the database connection pool.
 */
class LimitConcurrentMultiStatementRequests
{
    public const MAX_CONCURRENT_REQUESTS_PER_PLATFORM = 2;

    /**
     * Safety expiry for a slot in case the PHP process dies before releasing it.
     */
    public const SLOT_RELEASE_AFTER_SECONDS = 60;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return Redis::funnel(self::funnelKey($request))
            ->limit(self::MAX_CONCURRENT_REQUESTS_PER_PLATFORM)
            ->releaseAfter(self::SLOT_RELEASE_AFTER_SECONDS)
            ->block(0)
            ->then(
                static fn (): Response => $next($request),
                static fn (): Response => response('Limit Reached. Too many concurrent requests for this platform, please wait for your previous requests to complete', Response::HTTP_TOO_MANY_REQUESTS, ['Retry-After' => 1])
            );
    }

    /**
     * The hash tag keeps every slot key on the same Redis Cluster node, which the funnel's Lua script requires.
     */
    public static function funnelKey(Request $request): string
    {
        $user = $request->user();

        $owner = $user?->platform_id !== null
            ? 'platform:'.$user->platform_id
            : 'user:'.$user?->id;

        return 'multi-api-concurrency:{'.$owner.'}';
    }
}
