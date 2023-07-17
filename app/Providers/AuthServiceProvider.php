<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::viaRequest('eu-login', function ($request) {
            if (session()->get('cas_user')) {
                if (cas()->isAuthenticated()) {
                    $user =  User::firstOrCreateByAttributes(cas()->getAttributes());

                    $user->acceptInvitation();
                    return $user;
                } else return null;
            } else return null;
        });
    }
}
