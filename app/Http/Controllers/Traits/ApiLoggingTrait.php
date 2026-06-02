<?php

namespace App\Http\Controllers\Traits;

use App\Models\ApiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

trait ApiLoggingTrait
{
    private const MAX_LOGGED_BYTES = 65536;

    protected function logApiCall(
        Request $request,
        JsonResponse $response,
        ?int $platformId = null,
        ?Throwable $error = null
    ): void {
        $statusCode = $response->getStatusCode();
        $isError = $error !== null || $statusCode >= 400;
        $responseData = json_decode($response->getContent(), true) ?? [];

        ApiLog::create([
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'platform_id' => $platformId,
            'request_data' => $this->capPayload($request->all()),
            'response_data' => $this->prepareResponseData($responseData, $isError),
            'response_code' => $statusCode,
            'error_message' => $error ? $error->getMessage() : null,
        ]);
    }

    protected function handleApiOperation(Request $request, callable $operation, ?int $platformId = null): JsonResponse
    {
        $response = $operation();
        $this->logApiCall($request, $response, $platformId);

        return $response;
    }

    /**
     * Keep a successful response body when small, otherwise replace large
     * payloads (e.g. Elasticsearch results) with a compact summary. Large
     * error bodies are kept as a truncated preview so they remain debuggable.
     *
     * @param  array<array-key, mixed>  $payload
     * @return array<array-key, mixed>
     */
    private function prepareResponseData(array $payload, bool $isError): array
    {
        if ($payload === []) {
            return [];
        }

        $encoded = json_encode($payload);

        if ($encoded !== false && strlen($encoded) <= self::MAX_LOGGED_BYTES) {
            return $payload;
        }

        if ($isError) {
            return $this->truncatedPayload($encoded);
        }

        return [
            'logged_summary' => true,
            'response_size_bytes' => $encoded === false ? null : strlen($encoded),
            'top_level_keys' => array_slice(array_keys($payload), 0, 50),
        ];
    }

    /**
     * Store the payload as-is when small, otherwise a truncated preview.
     *
     * @param  array<array-key, mixed>  $payload
     * @return array<array-key, mixed>
     */
    private function capPayload(array $payload): array
    {
        $encoded = json_encode($payload);

        if ($encoded === false || strlen($encoded) <= self::MAX_LOGGED_BYTES) {
            return $payload;
        }

        return $this->truncatedPayload($encoded);
    }

    /**
     * @return array{truncated: bool, original_size_bytes: int, preview: string}
     */
    private function truncatedPayload(string $encoded): array
    {
        return [
            'truncated' => true,
            'original_size_bytes' => strlen($encoded),
            'preview' => substr($encoded, 0, self::MAX_LOGGED_BYTES),
        ];
    }
}
