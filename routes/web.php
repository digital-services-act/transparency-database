<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DayArchiveController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;


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

Route::middleware(['force.auth'])->group(function () {
    // Your routes that require authentication in non-production environments

    Route::middleware(['auth'])->group(function () {

        Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
        Route::post('feedback', [FeedbackController::class, 'send'])->name('feedback.send');

        Route::group(['middleware' => ['can:create statements']], function () {
            Route::get('/statement/create', [StatementController::class, 'create'])->name('statement.create');
            Route::post('/statement', [StatementController::class, 'store'])->name('statement.store');
        });

        Route::group(['middleware' => ['can:administrate']], function(){
            Route::prefix('/admin/')->group(function () {
                Route::resource('role', RoleController::class);
                Route::resource('permission', PermissionController::class);
                Route::resource('invitation', InvitationController::class);
                Route::resource('user', UserController::class);
                Route::resource('platform', PlatformController::class);

            });
        });

        Route::get('/profile/start', [ProfileController::class, 'profile'])->name('profile.start');
        Route::get('/profile/page/{page}', [PageController::class, 'profileShow'])->name('profile.page.show');

        Route::get('/profile/api', [ProfileController::class, 'apiIndex'])->name('profile.api.index');
        Route::post('/profile/api/new-token', [ProfileController::class, 'newToken'])->name('profile.api.new-token');

        // Register the Platform
        Route::get('/platform-register', [PlatformController::class, 'platformRegister'])->name('platform.register');
        Route::post('/platform-register', [PlatformController::class, 'platformRegisterStore'])->name('platform.register.store')->middleware(ProtectAgainstSpam::class);

    });


Route::get('/statement', [StatementController::class, 'index'])->name('statement.index');
Route::get('/statement/csv', [StatementController::class, 'exportCsv'])->name('statement.export');
Route::get('/statement-search', [StatementController::class, 'search'])->name('statement.search');
Route::get('/statement/{statement:uuid}', [StatementController::class, 'show'])->name('statement.show');

Route::get('/data-download/{uuid?}', [DayArchiveController::class, 'index'])->name('dayarchive.index');
Route::get('/daily-archives', function(){
    return Redirect::to('/data-download', 301);
});

Route::view('/', 'home')->name('home');
Route::get('/page/{page}', [PageController::class, 'show'])->name('page.show');
Route::view('/dashboard', 'dashboard')->name('dashboard');

Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
Route::get('/analytics/platforms', [AnalyticsController::class, 'platforms'])->name('analytics.platforms');
Route::get('/analytics/platform/{uuid?}', [AnalyticsController::class, 'forPlatform'])->name('analytics.platform');
Route::get('/analytics/restrictions', [AnalyticsController::class, 'restrictions'])->name('analytics.restrictions');
Route::get('/analytics/categories', [AnalyticsController::class, 'categories'])->name('analytics.categories');
Route::get('/analytics/category/{category?}', [AnalyticsController::class, 'forCategory'])->name('analytics.category');
Route::get('/analytics/grounds', [AnalyticsController::class, 'grounds'])->name('analytics.grounds');
Route::get('/analytics/keywords', [AnalyticsController::class, 'keywords'])->name('analytics.keywords');
Route::get('/analytics/keyword/{keyword?}', [AnalyticsController::class, 'forKeyword'])->name('analytics.keyword');

});
