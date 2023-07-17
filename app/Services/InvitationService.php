<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class InvitationService
{
    public function getPendingInvitation($email): ?Invitation
    {
        $invitation =  Invitation::firstWhere([
            'email' => $email
        ]);
        return $invitation;
    }


}
