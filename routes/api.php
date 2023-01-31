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

Route::get('notice/{notice}', ['App\Http\Controllers\Api\NoticeAPIController','show'])->middleware('auth:sanctum')->name('api.notice.show');
Route::post('notice/create', ['App\Http\Controllers\Api\NoticeAPIController','store'])->middleware('auth:sanctum')->name('api.notice.store');


