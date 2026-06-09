<?php

namespace App\Providers;

use App\Services\DayArchiveService;
use App\Services\PlatformQueryService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DayArchiveServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(DayArchiveService::class, static fn (Application $app) => new DayArchiveService(app(PlatformQueryService::class)));
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
        return [DayArchiveService::class];
    }
}
