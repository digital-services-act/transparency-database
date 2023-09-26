<?php

namespace App\Providers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use loophp\psr17\Psr17;
use loophp\psr17\Psr17Interface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            ClientInterface::class,
            function(Application $app): ClientInterface {
                //or whatever client you want
                return new Client();
            }
        );
        $this->app->bind(
            Psr17Interface::class,
            function(Application $app): Psr17Interface {
                $psr17Factory = new Psr17Factory();

                //or whatever psr17 you want
                return new Psr17(
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Blade::withoutDoubleEncoding();

        view()->share('ecl_init', true);
    }
}
