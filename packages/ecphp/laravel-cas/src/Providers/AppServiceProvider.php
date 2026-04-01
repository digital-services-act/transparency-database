<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\LaravelCas\Providers;

use EcPhp\CasLib\Cas;
use EcPhp\CasLib\Contract\CasInterface;
use EcPhp\CasLib\Contract\Configuration\PropertiesInterface;
use EcPhp\CasLib\Contract\Response\CasResponseBuilderInterface;
use EcPhp\CasLib\Contract\Response\Factory\AuthenticationFailureFactory as AuthenticationFailureFactoryInterface;
use EcPhp\CasLib\Contract\Response\Factory\ProxyFactory as ProxyFactoryInterface;
use EcPhp\CasLib\Contract\Response\Factory\ProxyFailureFactory as ProxyFailureFactoryInterface;
use EcPhp\CasLib\Contract\Response\Factory\ServiceValidateFactory as ServiceValidateFactoryInterface;
use EcPhp\CasLib\Response\CasResponseBuilder;
use EcPhp\CasLib\Response\Factory\AuthenticationFailureFactory;
use EcPhp\CasLib\Response\Factory\ProxyFactory;
use EcPhp\CasLib\Response\Factory\ProxyFailureFactory;
use EcPhp\CasLib\Response\Factory\ServiceValidateFactory;
use EcPhp\LaravelCas\Auth\CasGuard;
use EcPhp\LaravelCas\Auth\CasUserProvider;
use EcPhp\LaravelCas\Config\Laravel;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\ParameterBag;

use function dirname;

final class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes(
            [dirname(__DIR__) . '/publishers/config' => config_path()],
            'laravel-cas'
        );
        $this->app->router->group(
            ['namespace' => 'EcPhp\LaravelCas\Controllers'],
            static fn () => require dirname(__DIR__) . '/Config/routes.php'
        );
        Auth::provider(
            'laravel-cas',
            fn (): UserProvider => new CasUserProvider(app('session.store'))
        );
        Auth::extend(
            'laravel-cas',
            fn (Application $app, string $name, array $config): Guard => new CasGuard(Auth::createUserProvider($config['provider']), $app->make('request'), app('session.store'))
        );
    }

    public function register()
    {
        $this->app->bind(
            PropertiesInterface::class,
            static fn (Application $app): PropertiesInterface => new Laravel(
                new ParameterBag((array) config('laravel-cas')),
                $app->router
            )
        );
        $this->app->bind(
            CasResponseBuilderInterface::class,
            static fn (Application $app): CasResponseBuilder => $app->make(CasResponseBuilder::class)
        );
        $this->app->bind(
            CasInterface::class,
            static fn (Application $app): Cas => $app->make(Cas::class)
        );
        $this->app->bind(
            AuthenticationFailureFactoryInterface::class,
            static fn (Application $app): AuthenticationFailureFactory => $app->make(AuthenticationFailureFactory::class)
        );
        $this->app->bind(
            ProxyFactoryInterface::class,
            static fn (Application $app): ProxyFactory => $app->make(ProxyFactory::class)
        );
        $this->app->bind(
            ServiceValidateFactoryInterface::class,
            static fn (Application $app): ServiceValidateFactory => $app->make(ServiceValidateFactory::class)
        );
        $this->app->bind(
            ProxyFailureFactoryInterface::class,
            static fn (Application $app): ProxyFailureFactory => $app->make(ProxyFailureFactory::class)
        );
    }
}
