<?php

namespace App\Providers;

use App\Services\DayArchiveQueryService;
use App\Services\StatementQueryService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class DayArchiveQueryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DayArchiveQueryService::class, fn(Application $app) => new DayArchiveQueryService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [DayArchiveQueryService::class];
    }
}
