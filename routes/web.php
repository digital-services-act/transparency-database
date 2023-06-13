<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\UserController;
use App\Models\Statement;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


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



Route::middleware(['cas.auth'])->group(function() {

    Route::group(['middleware' => ['can:create statements']], function(){
        Route::get('/statement/create', [StatementController::class, 'create'])->name('statement.create');
        Route::post('/statement', [StatementController::class, 'store'])->name('statement.store');
    });


    Route::group(['middleware' => ['can:administrate']], function(){
        Route::resource('role', RoleController::class);
        Route::resource('permission', PermissionController::class);
        Route::resource('user', UserController::class);
        Route::resource('platform', PlatformController::class);
        Route::get('logs', [LogsController::class, 'index'])->name('logs')->can('view logs');
    });


    Route::group(['middleware' => ['can:view dashboard']], function(){
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/api', [DashboardController::class, 'apiIndex'])->name('api-index');
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports')->can('view reports');
        Route::post('/new-token', [DashboardController::class, 'newToken'])->name('new-token');
        Route::get('/dashboard/page/{page}', [PageController::class, 'dashboardShow'])->name('dashboard.page.show');

        Route::get('/request-platform', [UserController::class, 'requestPlatformForm'])->name('user.request.platform.form');
        Route::post('/request-platform', [UserController::class, 'requestPlatform'])->name('user.request.platform');
    });

});



Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/statement', [StatementController::class, 'index'])->name('statement.index');
Route::get('/statement-search', [StatementController::class, 'search'])->name('statement.search');
Route::get('/statement/{statement:uuid}', [StatementController::class, 'show'])->name('statement.show');

Route::get('/page/{page}', [PageController::class, 'show'])->name('page.show');


