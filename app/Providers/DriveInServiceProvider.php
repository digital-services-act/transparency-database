<?php

namespace App\Providers;

use App\Services\DriveInService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DriveInServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(DriveInService::class, static fn (Application $app) => new DriveInService);
    }

    /**
     * Get the services provided by the provider.
     *
     * @codeCoverageIgnore
     *
     * @return array<int, string>
     */
    #[\Override]
    public function provides(): array
    {
        return [DriveInService::class];
    }
}
