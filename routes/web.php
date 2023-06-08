<?php

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


    Route::get('/cache', function(){
        Cache::add('key', Carbon::now(), $seconds = 5);
        $value = Cache::get('key');
        return $value;
    })->name('cache');

    Route::group(['middleware' => ['can:create statements']], function(){
        Route::get('/statement/create', [\App\Http\Controllers\StatementController::class, 'create'])->name('statement.create');
        Route::post('/statement', [\App\Http\Controllers\StatementController::class, 'store'])->name('statement.store');
    });


    Route::group(['middleware' => ['can:administrate']], function(){
        Route::resource('role', \App\Http\Controllers\RoleController::class);
        Route::resource('permission', \App\Http\Controllers\PermissionController::class);
        Route::resource('user', \App\Http\Controllers\UserController::class);
        Route::resource('platform', \App\Http\Controllers\PlatformController::class);
        Route::get('logs', [\App\Http\Controllers\LogsController::class, 'index'])->name('logs')->can('view logs');
    });


//    Route::group(['middleware' => ['can:view dashboard']], function(){
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/api', [\App\Http\Controllers\DashboardController::class, 'apiIndex'])->name('api-index');
        Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports')->can('view reports');
        Route::post('/new-token', [\App\Http\Controllers\DashboardController::class, 'newToken'])->name('new-token');
        Route::get('/dashboard/page/{page}', [\App\Http\Controllers\PageController::class, 'dashboardShow'])->name('dashboard.page.show');
//    });

});



Route::get('/', function () {
    return view('home');
})->name('home');


Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
Route::get('/statement', [\App\Http\Controllers\StatementController::class, 'index'])->name('statement.index');
Route::get('/statement-search', [\App\Http\Controllers\StatementController::class, 'search'])->name('statement.search');
Route::get('/statement/{statement:uuid}', [\App\Http\Controllers\StatementController::class, 'show'])->name('statement.show');
//Route::get('/statement/{statement:uuid}/details', [\App\Http\Controllers\StatementController::class, 'show_details'])->name('statement.show-details');

Route::get('/page/{page}', [\App\Http\Controllers\PageController::class, 'show'])->name('page.show');


