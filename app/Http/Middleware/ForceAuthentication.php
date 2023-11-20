<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class ForceAuthentication
{
    public function handle($request, Closure $next)
    {
        // Check if the application is in dev, acc or sandbox
        if (!in_array(strtolower(config('app.env_real')), ['dev', 'acc', 'sandbox'])) {

            // Force authentication logic here
            if (!auth()->check()) {
                // Redirect to the dashboard page for authentication logic
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
