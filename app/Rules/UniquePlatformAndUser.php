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
        $platform = request('platform');
        $emails = request('emails');

        // Check the uniqueness for each combination
        foreach ($emails as $email) {
            if (User::where([['platform_id', "=", $platform->id], ['email', '=', $email]])
                ->exists()) {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function message()
    {
        return 'The email :input is already known in the system.';
    }
}
