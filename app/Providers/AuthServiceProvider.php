<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
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
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::before(static fn ($user, $ability) => $user->hasRole('Admin') ? true : null);

        // Add the new gate for API Key generation
        Gate::define('generate-api-key', function ($user) {
            return $user->hasAnyPermission(['generate API Key', 'create statements']);
        });
    }
}
