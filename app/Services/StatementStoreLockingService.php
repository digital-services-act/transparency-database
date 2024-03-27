<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class StatementStoreLockingService
{
    public int $wait = 30;
    public function getTheLocksFor(int $platform_id, array $puids): bool
    {
        $locks = [];
        foreach ($puids as $puid) {
            if (!Cache::lock($platform_id . '-' . $puid, $this->wait)->get())
            {
                return false;
            }
        }
        return true;
    }
}