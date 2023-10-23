<?php

namespace App\Providers;

use App\Services\ApplicationDateAggregateService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ApplicationDateAggregateServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ApplicationDateAggregateService::class, fn(Application $app) => new ApplicationDateAggregateService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [ApplicationDateAggregateService::class];
    }
}
