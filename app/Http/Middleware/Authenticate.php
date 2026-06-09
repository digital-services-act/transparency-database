<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
<<<<<<< HEAD
=======
     * Handle an unauthenticated user.
     *
     * API requests must always receive a JSON 401 response instead of being
     * redirected to the CAS login flow, even when no `Accept: application/json`
     * header is present.
     *
     * @param  array<int, string>  $guards
     */
    #[\Override]
    protected function unauthenticated($request, array $guards): void
    {
        if ($request->is('api/*')) {
            throw new HttpResponseException(
                response()->json(['message' => 'Unauthenticated.'], 401)
            );
        }

        parent::unauthenticated($request, $guards);
    }

    /**
>>>>>>> dev
     * Get the path the user should be redirected to when they are not authenticated.
     */
    #[\Override]
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('laravel-cas-login');
    }
}
