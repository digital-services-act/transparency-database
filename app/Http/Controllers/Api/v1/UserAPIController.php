<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Sanitizer;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UserAPIController extends Controller
{
    use Sanitizer;

    public function get($email)
    {
        // If user is still invited, he should be inactive
        Log::info("API - Get User - Start");
        //check if the user exists or fail so we get a 404
        $user = User::where('email', $email)->firstOrFail();

        // If user does exist, check for the token
        $is_active = $user->tokens->isNotEmpty();

        Log::info("API - Get User - Success");
        return response()->json(['active' => $is_active], Response::HTTP_OK);
    }

    public function delete($email)
    {
        Log::info("API - Delete User - Start");
        // Find the user by email or return a 404 response
        $user = User::where('email', $email)->firstOrFail();

        // Perform the delete operations
        $user->tokens()->delete();
        $user->delete();

        Log::info("API - Delete User - Success");
        // Return a success response with HTTP 204 No Content (indicating successful deletion)
        return response()->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }


}
