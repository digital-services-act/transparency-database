<?php

namespace App\Providers;

use App\Services\ContentDateAggregateService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ContentDateAggregateServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ContentDateAggregateService::class, fn(Application $app) => new ContentDateAggregateService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [ContentDateAggregateService::class];
    }
}
