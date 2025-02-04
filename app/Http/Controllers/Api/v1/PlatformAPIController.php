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
                
                // Check if a platform with the same name exists (case insensitive)
                $existingPlatform = Platform::whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])->first();
                
                if (!$existingPlatform) {
                    // If no existing platform found, create a new one
                    $platform = Platform::create($validated);
                    return response()->json($platform, Response::HTTP_CREATED);
                }
                
                // If platform exists and has a dsa_common_id, throw an exception
                if ($existingPlatform->dsa_common_id !== null) {
                    $validator = validator([], []);
                    $validator->errors()->add('name', 'A platform with this name already exists and has a DSA Common ID');
                    throw new \Illuminate\Validation\ValidationException($validator);
                }
                
                // Update the existing platform with any new data, but keep original values if not provided
                if (isset($validated['dsa_common_id'])) {
                    $existingPlatform->dsa_common_id = $validated['dsa_common_id'];
                    $existingPlatform->save();
                }
                return response()->json($existingPlatform, Response::HTTP_OK);
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
