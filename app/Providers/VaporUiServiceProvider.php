<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class VaporUiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->gate();
    }

    /**
     * Register the Vapor UI gate.
     *
     * This gate determines who can access Vapor UI in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewVaporUI', static fn(User $user = null) => in_array(optional($user)->email, [
            'Alain.VAN-DRIESSCHE@ext.ec.europa.eu',
            'Robert.BROWN@ext.ec.europa.eu'
        ]));
    }

    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        //
    }
}
