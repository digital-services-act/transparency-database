<?php

use App\Http\Controllers\Api\v2\StatementCHAPIController;
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

//Route::middleware('auth:sanctum')->group(function(){
//    Route::get('statement/{statement:uuid}', [StatementAPIController::class,'show'])->name('api.v2.statement.show')->can('view statements');
//    Route::post('statement', [StatementAPIController::class,'store'])->name('api.v2.statement.store')->can('create statements');
//});


Route::middleware('auth:sanctum')->group(static function () {
    Route::get('chstatement/{uuid}', [StatementCHAPIController::class, 'show'])->name('api.v2.chstatement.show')->can('view statements');
    Route::get('chstatement/existing-puid/{puid}', [StatementCHAPIController::class, 'existingPuid'])->name('api.v2.statement.existing-puid')->can('view statements');
    //Route::post('chstatement', [StatementCHAPIController::class, 'store'])->name('api.v2.chstatement.store')->can('create statements');
});

// Temporarily allow unauthenticated access to the store endpoint
Route::post('chstatement', [StatementCHAPIController::class, 'store'])->name('api.v2.chstatement.store');

