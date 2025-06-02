<?php

namespace App\Providers;

use App\Services\PlatformQueryService;
use App\Services\StatementCHSearchService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use ClickHouseDB\Client;

class StatementCHSearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $config = [
            'host' => config('clickhouse.connection.host'),
            'port' => config('clickhouse.connection.port'),
            'username' => config('clickhouse.connection.username'),
            'password' => config('clickhouse.connection.password'),
            'database' => config('clickhouse.connection.options.database')
        ];
        $db = new Client($config);
        $db->database(config('clickhouse.connection.options.database'));
        $db->setTimeout(config('clickhouse.connection.options.timeout'));       // 30 seconds
        $db->setConnectTimeOut(config('clickhouse.connection.options.connectTimeOut')); // 5 seconds
        //$db->ping(true); // if can`t connect throw exception  

        $this->app->singleton(StatementCHSearchService::class, static fn(Application $app) => new StatementCHSearchService($db, app(PlatformQueryService::class)));
    }

    /**
     * Get the services provided by the provider.
     *
     * @codeCoverageIgnore
     * @return array<int, string>
     */
    #[\Override]
    public function provides(): array
    {
        return [StatementCHSearchService::class];
    }
}
