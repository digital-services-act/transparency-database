<?php

namespace App\Providers;

use App\Services\PlatformQueryService;
use App\Services\StatementSearchService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use OpenSearch\Client;

class StatementSearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(StatementSearchService::class, static fn(Application $app) => new StatementSearchService(app(Client::class), app(PlatformQueryService::class)));
    }

    /**
     * Get the services provided by the provider.
     *
     * @codeCoverageIgnore
     * @return array<int, string>
     */
    #[\Override]
    public function provides(): array
    {
        return [StatementSearchService::class];
    }
}
