<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

Route::get('/', function () {
    return view('home');
})->name('home');


Route::get('/test/token', [\App\Http\Controllers\TestController::class, 'token'])->name('token');



Route::middleware(['cas.auth'])->group(function() {

    Route::get('/statement/create', [\App\Http\Controllers\StatementController::class, 'create'])->name('statement.create');
    Route::post('/statement', [\App\Http\Controllers\StatementController::class, 'store'])->name('statement.store');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('/impersonate', [\App\Http\Controllers\ImpersonateController::class, 'impersonate'])->name('impersonate');
    Route::get('/impersonate/stop', [\App\Http\Controllers\ImpersonateController::class, 'stopImpersonate'])->name('impersonate_stop');

    Route::resource('role', \App\Http\Controllers\RoleController::class);
    Route::resource('permission', \App\Http\Controllers\PermissionController::class);
    Route::resource('user', \App\Http\Controllers\UserController::class);

});

Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
Route::get('/statement', [\App\Http\Controllers\StatementController::class, 'index'])->name('statement.index');
Route::get('/statement/{statement}', [\App\Http\Controllers\StatementController::class, 'show'])->name('statement.show');

Route::get('/page/{page}', [\App\Http\Controllers\PageController::class, 'show'])->name('page.show');


Route::resource('entity', App\Http\Controllers\EntityController::class)->except('edit', 'update', 'destroy');

Route::get('/testteamslogging', function(){
    $message = 'Test is working! It is now: ' . Carbon::now();
    Log::info($message);
    return $message;
});


Route::get('/env', function(){
    $message = 'env("'.\request()->get('key', 'APP_ENV').'") -> ' . env(\request()->get('key', 'APP_ENV'));
    Log::info($message);
    return $message;
});


