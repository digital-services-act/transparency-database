<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ApiLoggingTrait;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Http\Requests\PlatformUsersStoreRequest;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PlatformUserAPIController extends Controller
{
    use ApiLoggingTrait, ExceptionHandlingTrait;

    public function store(PlatformUsersStoreRequest $request, Platform $platform): JsonResponse
    {
        return $this->handleApiOperation(
            $request,
            function () use ($request, $platform) {
                $validated = $request->validated();

                foreach ($validated['emails'] as $email) {
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        ['password' => bcrypt(random_int(0, mt_getrandmax()))]
                    );
                    $user->platform_id = $platform->id;
                    $user->save();
                    $user->assignRole('Contributor');
                }

                Log::info('API - Platform Users Store - Success');

                return response()->json($platform, Response::HTTP_CREATED);
            },
            $platform->id
        );
    }
}
