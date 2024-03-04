<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait StatementAPITrait
{

    protected function getRequestUserPlatformId(Request $request): ?int
    {
        return $request->user()->platform_id ?? null;
    }
}
