<?php

namespace App\Providers;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use App\Services\EuropeanLanguagesService;
use GuzzleHttp\Client;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use loophp\psr17\Psr17;
use loophp\psr17\Psr17Interface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use App\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    #[\Override]
    public function register(): void
    {
        $this->app->bind(
            ClientInterface::class,
            static fn(Application $app): ClientInterface => new Client()
        );
        $this->app->bind(
            Psr17Interface::class,
            static function (Application $app): Psr17Interface {
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
     * @codeCoverageIgnore
     * @return void
     */
    public function boot(): void
    {
        //if (strtolower((string) config('app.env_real')) === 'production') {
        Statement::disableSearchSyncing();
        //}

        Sanctum::usePersonalAccessTokenModel(
            PersonalAccessToken::class
        );

        Blade::withoutDoubleEncoding();
        view()->share('ecl_init', true);

        view()->composer('*', function ($view) {
            $languages = app(EuropeanLanguagesService::class)->getEuropeanLanguages();
            $view->with('languages', $languages);
        });

        // Analytics Float Format
        Blade::directive('aff', static fn(string $expression) => sprintf("<?php echo number_format(floatval(%s), 2, '.', '&nbsp;'); ?>", $expression));
        // Analytics Int Format
        Blade::directive('aif', static fn(string $expression) => sprintf("<?php echo number_format(intval(%s), 0, '', '&nbsp;'); ?>", $expression));
    }
}
