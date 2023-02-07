<?php

use App\Http\Controllers\Api\AuthController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('statement/{statement}', ['App\Http\Controllers\Api\StatementAPIController','show'])->middleware('auth:sanctum')->name('api.statement.show');
Route::post('statement/create', ['App\Http\Controllers\Api\StatementAPIController','store'])->middleware('auth:sanctum')->name('api.statement.store');


