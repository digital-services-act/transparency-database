<?php

namespace App\Providers;

use App\Services\StatementQueryService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class StatementQueryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(StatementQueryService::class, static fn(Application $app) => new StatementQueryService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    #[\Override]
    public function provides(): array
    {
        return [StatementQueryService::class];
    }
}
