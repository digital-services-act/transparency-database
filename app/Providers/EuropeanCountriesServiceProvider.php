<?php

namespace App\Providers;

use App\Services\EuropeanCountriesService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class EuropeanCountriesServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(EuropeanCountriesService::class, fn(Application $app) => new EuropeanCountriesService());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [EuropeanCountriesService::class];
    }
}
