<?php

use App\Models\Statement;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Support\Facades\Auth;
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
        Route::get('/statement/create', [\App\Http\Controllers\StatementController::class, 'create'])->name('statement.create');
        Route::post('/statement', [\App\Http\Controllers\StatementController::class, 'store'])->name('statement.store');
    });


    //Route::group(['middleware' => ['can:impersonate']], function(){
        Route::post('/impersonate', [\App\Http\Controllers\ImpersonateController::class, 'impersonate'])->name('impersonate');
        Route::get('/impersonate/stop', [\App\Http\Controllers\ImpersonateController::class, 'stopImpersonate'])->name('impersonate_stop');
    //});

    Route::group(['middleware' => ['can:administrate']], function(){
        Route::resource('role', \App\Http\Controllers\RoleController::class);
        Route::resource('permission', \App\Http\Controllers\PermissionController::class);
        Route::resource('user', \App\Http\Controllers\UserController::class);
        Route::get('logs', [\App\Http\Controllers\LogsController::class, 'index'])->name('logs')->can('view logs');
        Route::get('reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports')->can('view reports');
    });

    Route::group(['middleware' => ['can:view dashboard']], function(){
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'dashboard'])->name('dashboard');
        Route::post('/new-token', [\App\Http\Controllers\DashboardController::class, 'newToken'])->name('new-token');
    });

});


Route::get('/', function () {
    return view('home');
})->name('home');


Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
Route::get('/statement', [\App\Http\Controllers\StatementController::class, 'index'])->name('statement.index');
Route::get('/statement/{statement}', [\App\Http\Controllers\StatementController::class, 'show'])->name('statement.show');
Route::get('/page/{page}', [\App\Http\Controllers\PageController::class, 'show'])->name('page.show');


// What are we doing here?
Route::resource('entity', App\Http\Controllers\EntityController::class)->except('edit', 'update', 'destroy');




// Chain Saw Routes
//Route::get('/testteamslogging', function(){
//    $message = 'Test is working! It is now: ' . Carbon::now();
//    Log::info($message);
//    return $message;
//});
//
//
//Route::get('/env', function(){
//    $message = 'env("'.\request()->get('key', 'APP_ENV').'") -> ' . env(\request()->get('key', 'APP_ENV'));
//    Log::info($message);
//    return $message;
//});

Route::get('/reset-roles-and-permissions', function() {
    PermissionsSeeder::resetRolesAndPermissions();
    return "DONE";
});
Route::get('/make-a-bunch-of-statements', function(){
    Statement::factory()->count(500)->create();
    return "DONE";
});
Route::get('/make-a-bunch-of-users', function(){
    User::factory()->count(20)->create();
    return "DONE";
});

Route::get('/reset-entire-application', function() {
    User::query()->delete();
    User::factory()->count(20)->create();
    PermissionsSeeder::resetRolesAndPermissions();
    Statement::query()->delete();
    Statement::factory()->count(200)->create();
    session()->invalidate();

    $user = User::all()->random();
    session()->put('impersonate', $user->id);

    return redirect('/');
});


