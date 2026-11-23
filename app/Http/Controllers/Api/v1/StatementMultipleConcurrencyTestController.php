<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\LimitConcurrentMultiStatementRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Sleep;

/**
 * Non production only: holds a multi statement concurrency slot for a few seconds
 * so the per platform concurrency limit can be verified with parallel requests.
 */
class StatementMultipleConcurrencyTestController extends Controller
{
    public const DEFAULT_SECONDS = 5;

    public const MAX_SECONDS = 20;

    public function __invoke(Request $request): JsonResponse
    {
        $seconds = min(max((int) $request->input('seconds', self::DEFAULT_SECONDS), 0), self::MAX_SECONDS);

        $started_at = now();
        Sleep::for($seconds)->seconds();

        return response()->json([
            'funnel_key' => LimitConcurrentMultiStatementRequests::funnelKey($request),
            'max_concurrent_requests' => LimitConcurrentMultiStatementRequests::MAX_CONCURRENT_REQUESTS_PER_PLATFORM,
            'slept_seconds' => $seconds,
            'started_at' => $started_at->toIso8601String(),
            'finished_at' => now()->toIso8601String(),
        ]);
    }
}
