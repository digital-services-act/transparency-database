<?php

namespace App\Providers;

use App\Services\StatementStoreLockingService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class StatementStoreLockingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(StatementStoreLockingService::class, static fn(Application $app) => new StatementStoreLockingService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    #[\Override]
    public function provides(): array
    {
        return [StatementStoreLockingService::class];
    }
}
