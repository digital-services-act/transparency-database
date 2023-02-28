<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StatementAPIController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->group(function(){

    Route::get('statement/{statement}', [StatementAPIController::class,'show'])->name('api.statement.show')->can('view statements');
    Route::post('statement/create', [StatementAPIController::class,'store'])->name('api.statement.store')->can('create statements');

});

