<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformStoreRequest;
use App\Models\Platform;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PlatformAPIController extends Controller
{
    use ExceptionHandlingTrait;

    public function get(Platform $platform)
    {

        // Check if the application is in dev, acc or sandbox
        if (strtolower(config('app.env_real')) !== 'production') {
            $platform = Platform::withCount(['form_statements', 'api_statements', 'api_multi_statements'])->find($platform->id);
        }


        return response()->json($platform, Response::HTTP_OK);
    }



    public function store(PlatformStoreRequest $request): JsonResponse
    {

        $validated = $request->safe()->toArray();


        try {
            $platform = Platform::create($validated);
        } catch (QueryException $e) {

            return $this->handleQueryException($e, 'Platform');

        }



        return response()->json($platform, Response::HTTP_CREATED);
        }




    }
