<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Requests\PlatformUsersStoreRequest;
use App\Models\Invitation;
use App\Models\Platform;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class PlatformUserAPIController extends Controller
{
    use Sanitizer, ExceptionHandlingTrait;

    public function store(PlatformUsersStoreRequest $request, Platform $platform): JsonResponse
    {

        $validated = $request->validated();

        try {

            foreach ($validated['emails'] as $email) {
                Invitation::create([
                    "email" => $email,
                    "platform_id" => $platform->id
                ]);
            }

        } catch (QueryException $e) {

            return $this->handleQueryException($e, 'User');

        }


        return response()->json($platform, Response::HTTP_CREATED);
    }


}
