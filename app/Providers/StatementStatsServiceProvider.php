<?php

namespace App\Providers;

use App\Services\StatementStatsService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class StatementStatsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(StatementStatsService::class, fn(Application $app) => new StatementStatsService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [StatementStatsService::class];
    }
}
