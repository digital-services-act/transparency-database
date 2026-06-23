<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * @codeCoverageIgnore
 */
class RouteServiceProvider extends ServiceProvider
{
    private const AUTHENTICATED_API_REQUESTS_PER_SECOND = 200;

    private const AUTHENTICATED_API_REQUESTS_PER_MINUTE = 12000;

    private const ELEVATED_WEB_DOWNLOAD_ROUTES = [
        'aggregates.download',
        'dayarchive.download',
        'dayarchive.download.filename',
        'dayarchive.download.filename.sha1',
    ];

    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    protected $apiNamespace = 'App\Http\Controllers\Api';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    #[\Override]
    public function boot(): void
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
        RateLimiter::for('api', static function (Request $request): Limit|array {
            $response = static fn (Request $request, array $headers) => response('Limit Reached. Please do not overload the API', 429, $headers);
            $user = $request->user();

            if (! $user) {
                return Limit::perMinute(100)
                    ->by($request->ip())
                    ->response($response);
            }

            return [
                Limit::perSecond(self::AUTHENTICATED_API_REQUESTS_PER_SECOND)
                    ->by('second:user:'.$user->id)
                    ->response($response),
                Limit::perMinute(self::AUTHENTICATED_API_REQUESTS_PER_MINUTE)
                    ->by('minute:user:'.$user->id)
                    ->response($response),
            ];
        });

        RateLimiter::for('web', static function (Request $request) {
            $isElevatedDownloadRoute = $request->routeIs(...self::ELEVATED_WEB_DOWNLOAD_ROUTES);

            return self::webRateLimit(
                $request,
                $isElevatedDownloadRoute ? 200 : ($request->user() ? 50 : 20),
                $isElevatedDownloadRoute ? 'downloads' : 'general',
            );
        });
    }

    private static function webRateLimit(Request $request, int $maxAttempts, string $bucket): Limit
    {
        $identifier = $request->user()
            ? 'user:'.$request->user()->id
            : 'ip:'.$request->ip();

        return Limit::perMinute($maxAttempts)
            ->by($bucket.':'.$identifier)
            ->response(static fn (Request $request, array $headers) => response('Limit Reached. Please do not overload the application', 429, $headers));
    }

    /**v
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
                'namespace' => $this->apiNamespace.'\v'.$v,
                'prefix' => 'api/v'.$v,
            ], static function ($router) use ($v) {
                require base_path('routes/api_v'.$v.'.php');
            });
        }
    }
}
