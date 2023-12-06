<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Requests\PlatformStoreRequest;
use App\Http\Requests\StatementsStoreRequest;
use App\Http\Requests\StatementStoreRequest;
use App\Http\Resources\PlatformResource;
use App\Models\Platform;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlatformAPIController extends Controller
{
    use Sanitizer;

    public function get(Platform $platform)
    {

        // Check if the application is in dev, acc or sandbox
        if (strtolower(config('app.env_real')) !== 'production') {
            $platform = Platform::withCount(['form_statements', 'api_statements', 'api_multi_statements'])->find($platform->id);
        }
        $out = new PlatformResource($platform);


        return response()->json($out, Response::HTTP_OK);
    }

    public function store(PlatformStoreRequest $request): JsonResponse
    {

        $validated = $request->safe()->merge([

        ])->toArray();

        try {
            $platform = Platform::create($validated);
        } catch (QueryException $e) {


            Log::error('Statement Creation Query Exception Thrown: ' . $e->getMessage());
            $errors = [
                'uncaught_exception' => [
                    'Statement Creation Query Exception Thrown: ' . $e->getMessage()
                ]
            ];
            $message = 'Statement Creation Query Exception Thrown: ' . $e->getMessage();

            return response()->json(['message' => $message, 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);

        }


        $out = new PlatformResource($platform);

        return response()->json($out, Response::HTTP_CREATED);
        }




    }
