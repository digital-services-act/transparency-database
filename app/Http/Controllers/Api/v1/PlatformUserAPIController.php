<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Requests\PlatformStoreRequest;
use App\Http\Requests\PlatformUsersStoreRequest;
use App\Http\Requests\StatementsStoreRequest;
use App\Http\Requests\StatementStoreRequest;
use App\Http\Resources\PlatformResource;
use App\Models\Invitation;
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

class PlatformUserAPIController extends Controller
{
    use Sanitizer;


    public function store(PlatformUsersStoreRequest $request, Platform $platform): JsonResponse
    {

        $validated = $request->validated();

        try {

        foreach($validated['emails'] as $email){
            Invitation::create([
                "email" => $email,
                "platform_id" => $platform->id
            ]);
        }

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
