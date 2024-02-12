<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


/**
 * Class APIVersion
 * @package App\Http\Middleware
 */
class APIVersion
{
    /**
     * Handle an incoming request.
     *
     * @param $guard
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard)
    {
        config(['app.api.version' => $guard]);
        return $next($request);
    }
}
