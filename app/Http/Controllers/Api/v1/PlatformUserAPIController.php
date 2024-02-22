<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformUsersStoreRequest;
use App\Models\Invitation;
use App\Models\Platform;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;


class PlatformUserAPIController extends Controller
{
    use ExceptionHandlingTrait;

    public function store(PlatformUsersStoreRequest $request, Platform $platform): JsonResponse
    {
        $validated = $request->validated();

        try {
            foreach ($validated['emails'] as $email) {
                $user = User::firstOrCreate(
                    ['email' => $email],
                    ['password' => bcrypt(random_int(0, mt_getrandmax()))]
                );
                $user->platform_id = $platform->id;
                $user->save();
                $user->assignRole('Contributor');
            }
        } catch (QueryException $queryException) {
            return $this->handleQueryException($queryException, 'User');
        }


        return response()->json($platform, Response::HTTP_CREATED);
    }


}
