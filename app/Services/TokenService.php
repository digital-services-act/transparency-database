<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\User;

class TokenService
{
    public function getTotalVlopValidTokens()
    {
        $dsa_team_platform_id = Platform::dsaTeamPlatformId();
        return User::join('personal_access_tokens', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->join('platforms', 'platforms.id', '=', 'users.platform_id')
            ->where('platforms.vlop', 1)
            ->whereNot('platforms.id', $dsa_team_platform_id)
            ->whereNull('users.deleted_at')
            ->distinct()
            ->count('users.id');
    }

    public function getTotalNonVlopValidTokens()
    {
        $dsa_team_platform_id = Platform::dsaTeamPlatformId();
        return User::join('personal_access_tokens', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->join('platforms', 'platforms.id', '=', 'users.platform_id')
            ->where('platforms.vlop', 0)
            ->whereNot('platforms.id', $dsa_team_platform_id)
            ->whereNull('users.deleted_at')
            ->distinct()
            ->count('users.id');
    }
}
