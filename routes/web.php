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
//
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'dashboard'])
    ->middleware('cas.auth')
    ->name('dashboard');




Route::resource('entity', App\Http\Controllers\EntityController::class)->except('edit', 'update', 'destroy');

Route::resource('notice', App\Http\Controllers\NoticeController::class)->except('edit', 'update', 'destroy');

