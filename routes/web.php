<?php


use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogMessagesController;
use App\Http\Controllers\DayArchiveController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\UserController;
use App\Http\Requests\FeedbackSendRequest;
use App\Http\Requests\PlatformRegisterStoreRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
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

Route::middleware(['force.auth'])->group(static function () {
    // Your routes that require authentication in non-production environments
    Route::middleware(['auth'])->group(static function () {
        Route::get('feedback', static fn(Request $request) => (new FeedbackController())->index($request))->name('feedback.index');
        Route::post('feedback', static fn(FeedbackSendRequest $request) => (new FeedbackController())->send($request))->name('feedback.send');
        Route::group(['middleware' => ['can:create statements']], static function () {
            Route::get('/statement/create', [StatementController::class, 'create'])->name('statement.create');
            Route::post('/statement', [StatementController::class, 'store'])->name('statement.store');
        });
        Route::group(['middleware' => ['can:administrate']], static function () {
            Route::prefix('/admin/')->group(static function () {
//                Route::resource('role', RoleController::class);
//                Route::resource('permission', PermissionController::class);
                Route::delete('log-messages', [LogMessagesController::class, 'destroy'])->name('log-messages.destroy');
            });
        });

        Route::resource('user', UserController::class, ['middleware' => ['can:create users']]);
        Route::resource('platform', PlatformController::class, ['middleware' => ['can:create platforms']]);

        Route::get('/admin/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index')->can('view platforms');
        Route::get('/admin/log-messages', [LogMessagesController::class, 'index'])->name('log-messages.index')->can('view logs');

        Route::get('/profile/start', [ProfileController::class, 'profile'])->name('profile.start');

        Route::get('/profile/page/{page}', [PageController::class, 'profileShow'])->name('profile.page.show');
        Route::get('/profile/api', [ProfileController::class, 'apiIndex'])->name('profile.api.index')->can('create statements');
        Route::post('/profile/api/new-token', [ProfileController::class, 'newToken'])->name('profile.api.new-token')->can('create statements');

});


    Route::get('/statement', [StatementController::class, 'index'])->name('statement.index');

    Route::get('/statement/csv', [StatementController::class, 'exportCsv'])->name('statement.export');
    Route::get('/statement-search', [StatementController::class, 'search'])->name('statement.search');
    Route::get('/statement/{statement}', [StatementController::class, 'show'])
        ->where('statement', '[0-9]+')  // Only accept digits for a statement
        ->name('statement.show');
    Route::get('/statement/uuid/{uuid}', [StatementController::class, 'showUuid'])->name('statement.show.uuid');
    Route::get('/data-download/{uuid?}', [DayArchiveController::class, 'index'])->name('dayarchive.index');
    Route::get('/daily-archives', static fn() => Redirect::to('/data-download', 301));
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/page/{page}', static fn(string $page, bool $profile = false): Application|Factory|View|RedirectResponse|Redirector => (new PageController())->show($page, $profile))->name('page.show');
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('setlocale', function (Request $request) {
        if(!config('dsa.TRANSLATIONS')) return back();
        $locale = $request->input('locale');
        if (in_array($locale, config('app.locales'))) {
            session(['locale' => $locale]);
            session(['force_lang' => true]);
        }
        return back();
    })->name('setlocale');
});
