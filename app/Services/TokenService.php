<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TokenService
{
    public function getTotalVlopValidTokens()
    {
        return User::join('personal_access_tokens', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->join('platforms', 'platforms.id', '=', 'users.platform_id')
            ->where('platforms.vlop', 1)
            ->whereNot('platforms.name', 'DSA Team')
            ->whereNull('users.deleted_at')
            ->count('users.id');
    }

    public function getTotalNonVlopValidTokens()
    {
        return User::join('personal_access_tokens', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->join('platforms', 'platforms.id', '=', 'users.platform_id')
            ->where('platforms.vlop', 0)
            ->whereNull('users.deleted_at')
            ->count('users.id');
    }
}
