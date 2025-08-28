<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait StatementAPITrait
{
    protected function getRequestUserPlatformId(Request $request): ?int
    {
        return $request->user()->platform_id ?? null;
    }
}
