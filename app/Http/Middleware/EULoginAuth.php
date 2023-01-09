<?php

namespace App\Http\Middleware;


use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Guard;

class EULoginAuth
{
    protected $auth;
    protected $cas;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->cas = app('cas');
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $email
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->cas->checkAuthentication()) {

            // Store the user credentials in a Laravel managed session
            session()->put('cas_user', $this->cas->user());

            if (cas()->isMasquerading()) {
                $user = User::where('eu_login_username', $this->cas->user())
                    ->first();

                if (!$user) {
                    $user = User::factory()->make([
                        'domain' => 'external',
                        'domainUsername' => $this->cas->user(),
                        'eu_login_username' => $this->cas->user(),
                    ]);
                }

                cas()->setAttributes(
                    $user->toArray()
                );
            }

            // session()->put('cas_attributes', cas()->getAttributes());
        } else {

            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }
            $this->cas->authenticate();

        }

        return $next($request);
    }

}
