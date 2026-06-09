<?php

namespace App\Providers;

use App\Services\EuropeanLanguagesService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class EuropeanLanguagesServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(EuropeanLanguagesService::class, static fn (Application $app) => new EuropeanLanguagesService);
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
        return [EuropeanLanguagesService::class];
    }
}
