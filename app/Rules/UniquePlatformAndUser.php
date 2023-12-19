<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class UniquePlatformAndUser implements Rule
{
    public function passes($attribute, $value)
    {

        $platform = request('platform');
        $emails = request('emails');

        // Check the uniqueness for each combination
        foreach ($emails as $email) {
            if (User::where('platform_id', $platform->id)
                ->where('email', $email)
                ->exists()) {
                return false;
            }
        }

        return true;


    }

    public function message()
    {
        return 'The combination of platform and user already exists.';
    }
}
