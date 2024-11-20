<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformStoreRequest;
use App\Http\Requests\PlatformUpdateRequest;
use App\Models\Platform;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PlatformAPIController extends Controller
{
    use ExceptionHandlingTrait;

    public function get(Platform $platform): JsonResponse
    {

        // Check if the application is in dev, acc or sandbox
        if (strtolower((string) config('app.env_real')) !== 'production') {
            $platform = Platform::withCount(['form_statements', 'api_statements', 'api_multi_statements'])->find($platform->id);
        }


        return response()->json($platform, Response::HTTP_OK);
    }


    public function store(PlatformStoreRequest $request): JsonResponse
    {
        Log::info("API - Platform Store - Start");
        $validated = $request->safe()->toArray();
        Log::info($validated);

        try {
            $platform = Platform::create($validated);
        } catch (QueryException $queryException) {
            return $this->handleQueryException($queryException, 'Platform');
        }

        Log::info("API - Platform Store - Success");
        return response()->json($platform, Response::HTTP_CREATED);
    }

    public function update(Platform $platform, PlatformUpdateRequest $request): JsonResponse
    {
        Log::info("API - Platform Update - Start");
        $validated = $request->safe()->toArray();
        Log::info($validated);
        $platform->name = $validated['name'];
        $platform->vlop = $validated['vlop'];
        $platform->save();
        Log::info("API - Platform Update - Success");
        return response()->json($platform, Response::HTTP_OK);
    }


}
