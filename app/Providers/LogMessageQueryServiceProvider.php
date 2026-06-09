<?php

namespace App\Providers;

use App\Services\LogMessageQueryService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class LogMessageQueryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(LogMessageQueryService::class, static fn (Application $app) => new LogMessageQueryService);
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
        return [LogMessageQueryService::class];
    }
}
