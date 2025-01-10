<?php

namespace App\Http\Controllers\Traits;

use App\Models\ApiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

trait ApiLoggingTrait
{
    protected function logApiCall(
        Request $request,
        JsonResponse $response,
        ?int $platformId = null,
        ?Throwable $error = null
    ): void {
        ApiLog::create([
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'platform_id' => $platformId,
            'request_data' => $request->all(),
            'response_data' => json_decode($response->getContent(), true) ?? [],
            'response_code' => $response->getStatusCode(),
            'error_message' => $error ? $error->getMessage() : null,
        ]);
    }

    protected function handleApiOperation(Request $request, callable $operation, ?int $platformId = null): JsonResponse
    {
        $response = $operation();
        $this->logApiCall($request, $response, $platformId);
        return $response;
    }
}
