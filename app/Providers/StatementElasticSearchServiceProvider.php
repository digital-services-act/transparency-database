<?php

namespace App\Providers;

use App\Services\PlatformQueryService;
use App\Services\StatementElasticConnectionService;
use App\Services\StatementElasticIndexerService;
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
        $this->app->singleton(StatementElasticConnectionService::class, static fn () => new StatementElasticConnectionService);
        $this->app->singleton(StatementElasticSearchService::class, static fn (Application $app) => new StatementElasticSearchService(
            app(PlatformQueryService::class),
            $app->make(StatementElasticConnectionService::class),
        ));
        $this->app->singleton(StatementElasticIndexerService::class, static fn (Application $app) => new StatementElasticIndexerService(
            $app->make(StatementElasticConnectionService::class),
        ));
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
        return [
            StatementElasticConnectionService::class,
            StatementElasticSearchService::class,
            StatementElasticIndexerService::class,
        ];
    }
}
