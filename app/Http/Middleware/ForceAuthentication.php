<?php

namespace App\Http\Middleware;

use Closure;

class ForceAuthentication
{
    public function handle($request, Closure $next)
    {
        // Check if the application is in dev, acc or sandbox
        // Force authentication logic here
        if (in_array(strtolower((string) config('app.env')), ['dev', 'acc', 'sandbox']) && ! auth()->check()) {
            // Redirect to the dashboard page for authentication logic
            return redirect('/profile/start');
        }

        return $next($request);
    }
}
