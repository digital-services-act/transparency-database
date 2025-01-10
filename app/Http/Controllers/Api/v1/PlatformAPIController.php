<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformStoreRequest;
use App\Http\Requests\PlatformUpdateRequest;
use App\Models\Platform;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Http\Controllers\Traits\ApiLoggingTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlatformAPIController extends Controller
{
    use ExceptionHandlingTrait, ApiLoggingTrait;

    public function get(Platform $platform): JsonResponse
    {
        return $this->handleApiOperation(
            request(),
            function () use ($platform) {
                if (strtolower((string) config('app.env_real')) !== 'production') {
                    $platform = Platform::withCount(['form_statements', 'api_statements', 'api_multi_statements'])->find($platform->id);
                }
                return response()->json($platform, Response::HTTP_OK);
            },
            $platform->id
        );
    }

    public function store(PlatformStoreRequest $request): JsonResponse
    {
        return $this->handleApiOperation(
            $request,
            function () use ($request) {
                $validated = $request->safe()->toArray();
                $platform = Platform::create($validated);
                return response()->json($platform, Response::HTTP_CREATED);
            },
            null
        );
    }

    public function update(Platform $platform, PlatformUpdateRequest $request): JsonResponse
    {
        return $this->handleApiOperation(
            $request,
            function () use ($platform, $request) {
                $validated = $request->safe()->toArray();
                $platform->name = $validated['name'];
                $platform->vlop = $validated['vlop'];
                $platform->save();
                return response()->json($platform, Response::HTTP_OK);
            },
            $platform->id
        );
    }
}
