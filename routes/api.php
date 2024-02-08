<?php

use App\Http\Controllers\Api\v1\StatementAPIController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// These are unversioned api routes

//Route::middleware('auth:sanctum')->group(function(){
//    Route::get('statement/{statement:uuid}', [StatementAPIController::class,'show'])->name('api.statement.show')->can('view statements');
//    Route::post('statement', [StatementAPIController::class,'store'])->name('api.statement.store')->can('create statements');
//});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('ping', fn() => response()->json(["you_say" => "ping", "i_say" => "pong"]));

    Route::get('user', fn() => auth()->user())->can('create statements');
});
