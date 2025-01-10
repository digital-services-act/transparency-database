<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UniquePlatformAndUser implements Rule
{
    #[\Override]
    public function passes($attribute, $value)
    {
        $user = User::where('email', $value)->first();
        
        // If user doesn't exist, it's valid
        if (!$user) {
            return true;
        }

        // If user exists but has no platform, it's valid (will be assigned to this platform)
        if ($user->platform_id === null) {
            return true;
        }

        // If user exists and has a platform, it's invalid
        return false;
    }

    #[\Override]
    public function message()
    {
        return 'The email :input is already known in the system.';
    }
}
