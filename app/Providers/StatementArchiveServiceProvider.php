<?php

namespace App\Providers;

use App\Services\StatementArchiveService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;


class StatementArchiveServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(StatementArchiveService::class, static fn(Application $app) => new StatementArchiveService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [StatementArchiveService::class];
    }
}
