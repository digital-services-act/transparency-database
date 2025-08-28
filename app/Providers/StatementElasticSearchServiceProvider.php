<?php

namespace App\Providers;

use App\Services\PlatformQueryService;
use App\Services\StatementElasticSearchService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class StatementElasticSearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(StatementElasticSearchService::class, static fn (Application $app) => new StatementElasticSearchService(app(PlatformQueryService::class)));
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
        return [StatementElasticSearchService::class];
    }
}
