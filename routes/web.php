<?php

use App\Models\User;
use Illuminate\Http\Request;
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

Route::get('/notice/create', [\App\Http\Controllers\NoticeController::class, 'create'])->name('notice.create')->middleware('cas.auth');

Route::middleware(['cas.auth'])->group(function() {

    Route::post('/notice', [\App\Http\Controllers\NoticeController::class, 'store'])->name('notice.store');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'dashboard'])->name('dashboard');
});

Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
Route::get('/notice', [\App\Http\Controllers\NoticeController::class, 'index'])->name('notice.index');
Route::get('/notice/{notice}', [\App\Http\Controllers\NoticeController::class, 'show'])->name('notice.show');

Route::get('/page/{page}', [\App\Http\Controllers\PageController::class, 'show'])->name('page.show');


Route::resource('entity', App\Http\Controllers\EntityController::class)->except('edit', 'update', 'destroy');


