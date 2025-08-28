<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Class APIVersion
 */
class APIVersion
{
    /**
     * Handle an incoming request.
     *
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard)
    {
        config(['app.api.version' => $guard]);

        return $next($request);
    }
}
