<?php

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogMessagesController;
use App\Http\Controllers\DataDownloadController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['force.auth'])->group(static function () {
    // Your routes that require authentication in non-production environments
    Route::middleware(['auth'])->group(static function () {
        Route::get('feedback',[FeedbackController::class, 'index'])->name('feedback.index');
        Route::post('feedback', [FeedbackController::class, 'send'])->name('feedback.send');
        Route::group(['middleware' => ['can:create statements']], static function () {
            Route::get('/statement/create', [StatementController::class, 'create'])->name('statement.create');
            Route::post('/statement', [StatementController::class, 'store'])->name('statement.store');
        });

        Route::prefix('/admin/')->group(static function () {
            Route::group(['middleware' => ['can:administrate']], static function () {
                Route::delete('log-messages', [LogMessagesController::class, 'destroy'])->name('log-messages.destroy');
            });
            Route::get('onboarding', [OnboardingController::class, 'index'])->name('onboarding.index')->can('view platforms');
            Route::get('log-messages', [LogMessagesController::class, 'index'])->name('log-messages.index')->can('view logs');
        });

        Route::resource('user', UserController::class, ['middleware' => ['can:create users']]);
        Route::resource('platform', PlatformController::class, ['middleware' => ['can:create platforms']]);

        Route::get('/profile/start', [ProfileController::class, 'profile'])->name('profile.start');
        Route::get('/profile/page/{page}', [PageController::class, 'profileShow'])->name('profile.page.show');
        Route::get('/profile/api', [ProfileController::class, 'apiIndex'])->name('profile.api.index')->can('generate-api-key');
        Route::post('/profile/api/new-token', [ProfileController::class, 'newToken'])->name('profile.api.new-token')->can('generate-api-key');

    });


    Route::get('/statement', [StatementController::class, 'index'])->name('statement.index');

    Route::get('/statement/csv', [StatementController::class, 'exportCsv'])->name('statement.export');
    Route::get('/statement-search', [StatementController::class, 'search'])->name('statement.search');
    Route::get('/statement/{statement}', [StatementController::class, 'show'])
        ->where('statement', '[0-9]+')  // Only accept digits for a statement
        ->name('statement.show');
    Route::get('/statement/uuid/{uuid}', [StatementController::class, 'showUuid'])->name('statement.show.uuid');

    Route::get('/explore-data/download/{uuid?}', [DataDownloadController::class, 'index'])->name('dayarchive.index');

    Route::view('/explore-data/overview', 'explore-data.overview')->name('explore-data.overview');
    Route::view('/explore-data/toolbox', 'explore-data.toolbox')->name('explore-data.toolbox');

    Route::get('/daily-archives', static fn() => Redirect::to(route('dayarchive.index'), 301));
    Route::get('/data-download', static fn() => Redirect::to(route('dayarchive.index'), 301));

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/page/{page}', [PageController::class, 'show'])->name('page.show');
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/ping-redis', function () {
        try {
            Redis::ping();
            $keys = Redis::command('DBSIZE');
            return 'Successfully connected to Redis! Number of records: ' . $keys;
        } catch (\Exception $e) {
            return 'Failed to connect to Redis: ' . $e->getMessage();
        }
    });

});
