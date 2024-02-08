<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\HomeController;
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

        Route::get('feedback', fn(\Illuminate\Http\Request $request) => (new \App\Http\Controllers\FeedbackController())->index($request))->name('feedback.index');
        Route::post('feedback', fn(\App\Http\Requests\FeedbackSendRequest $request) => (new \App\Http\Controllers\FeedbackController())->send($request))->name('feedback.send');

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

        Route::get('/profile/start', fn(\Illuminate\Http\Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => (new \App\Http\Controllers\ProfileController())->profile($request))->name('profile.start');
        Route::get('/profile/page/{page}', fn(string $page): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application => (new \App\Http\Controllers\PageController())->profileShow($page))->name('profile.page.show');

        Route::get('/profile/api', fn(\Illuminate\Http\Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View => (new \App\Http\Controllers\ProfileController())->apiIndex($request))->name('profile.api.index');
        Route::post('/profile/api/new-token', fn(\Illuminate\Http\Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector => (new \App\Http\Controllers\ProfileController())->newToken($request))->name('profile.api.new-token');

        // Register the Platform
        Route::get('/platform-register', fn(\Illuminate\Http\Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse => (new \App\Http\Controllers\PlatformController())->platformRegister($request))->name('platform.register');
        Route::post('/platform-register', fn(\App\Http\Requests\PlatformRegisterStoreRequest $request): \Illuminate\Http\RedirectResponse => (new \App\Http\Controllers\PlatformController())->platformRegisterStore($request))->name('platform.register.store')->middleware(ProtectAgainstSpam::class);

    });


Route::get('/statement', [StatementController::class, 'index'])->name('statement.index');
Route::get('/statement/csv', [StatementController::class, 'exportCsv'])->name('statement.export');
Route::get('/statement-search', [StatementController::class, 'search'])->name('statement.search');
Route::get('/statement/{statement:uuid}', [StatementController::class, 'show'])->name('statement.show');

Route::get('/data-download/{uuid?}', [DayArchiveController::class, 'index'])->name('dayarchive.index');
Route::get('/daily-archives', fn() => Redirect::to('/data-download', 301));

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/page/{page}', fn(string $page, bool $profile = false): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector => (new \App\Http\Controllers\PageController())->show($page, $profile))->name('page.show');
Route::view('/dashboard', 'dashboard')->name('dashboard');

});
