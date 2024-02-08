<?php

namespace App\Providers;

use App\Services\DayArchiveService;
use App\Services\StatementSearchService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class DayArchiveServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(DayArchiveService::class, static fn(Application $app) => new DayArchiveService(app(StatementSearchService::class)));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    #[\Override]
    public function provides(): array
    {
        return [DayArchiveService::class];
    }
}
