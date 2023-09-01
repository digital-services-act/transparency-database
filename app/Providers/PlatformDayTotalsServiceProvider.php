<?php

namespace App\Providers;

use App\Services\PlatformDayTotalsService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class PlatformDayTotalsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PlatformDayTotalsService::class, fn(Application $app) => new PlatformDayTotalsService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [PlatformDayTotalsService::class];
    }
}
