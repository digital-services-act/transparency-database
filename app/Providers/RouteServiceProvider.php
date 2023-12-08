<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';


    protected $apiNamespace ='App\Http\Controllers\Api';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            $this->mapApiVersionedRoutes();

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return $request->user() ? Limit::perMinute(6000)->by($request->user()->id) : Limit::perMinute(20)->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('Limit Reached. Please do not overload the API', 429, $headers);
                });
        });

        RateLimiter::for('web', function (Request $request) {
            return $request->user() ? Limit::perMinute(50)->by($request->user()->id) : Limit::perMinute(20)->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('Limit Reached. Please do not overload the application', 429, $headers);
                });
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiVersionedRoutes()
    {
        $versions = config('app.api_versions');

        foreach ($versions as $v) {
            Route::group([
                'middleware' => ['api', 'api_version:v'.$v],
                'namespace'  => "{$this->apiNamespace}\\v".$v,
                'prefix'     => 'api/v'.$v,
            ], function ($router) use($v) {
                require base_path('routes/api_v'.$v.'.php');
            });
        }
    }
}
