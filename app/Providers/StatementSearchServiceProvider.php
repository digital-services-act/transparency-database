<?php

namespace App\Providers;

use App\Services\StatementSearchService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use OpenSearch\Client;

class StatementSearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(StatementSearchService::class, static fn(Application $app) => new StatementSearchService(app(Client::class)));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    #[\Override]
    public function provides(): array
    {
        return [StatementSearchService::class];
    }
}
