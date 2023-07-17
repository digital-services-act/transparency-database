<?php

namespace App\Providers;

use App\Services\StatementSearchService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class StatementSearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(StatementSearchService::class, fn(Application $app) => new StatementSearchService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [StatementSearchService::class];
    }
}
