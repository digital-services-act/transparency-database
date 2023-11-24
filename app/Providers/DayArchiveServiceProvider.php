<?php

namespace App\Providers;

use App\Services\DayArchiveService;
use App\Services\PlatformDayTotalsService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class DayArchiveServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DayArchiveService::class, fn(Application $app) => new DayArchiveService(app(PlatformDayTotalsService::class)));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [DayArchiveService::class];
    }
}
