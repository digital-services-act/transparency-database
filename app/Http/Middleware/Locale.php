<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Support\Facades\Validator;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $lang = config('app.locale');

        if ($request->has('lang')) {
            $validator = Validator::make($request->all(), [
                'lang' => ['required', 'string', 'in:' . implode(',', config('app.locales'))]
            ]);

            if ($validator->passes()) {
                $lang = strtolower(trim($request->input('lang')));
            }
        }

        session(['locale' => $lang]);

        return $next($request);
    }
}
