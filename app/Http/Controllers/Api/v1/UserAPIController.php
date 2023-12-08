<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Sanitizer;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Response;

class UserAPIController extends Controller
{
    use Sanitizer;

    public function get($email)
    {
        // If user is still invited, he should be inactive
        if (Invitation::where('email', $email)->exists()) {
            $is_active = false;
        } else {
            //check if the user exists or fail so we get a 404
            $user = User::where('email', $email)->firstOrFail();

            // If user does exist, check for the token
            $is_active = $user->tokens->isNotEmpty();
        }


        return response()->json(['active' => $is_active], Response::HTTP_OK);
    }


}
