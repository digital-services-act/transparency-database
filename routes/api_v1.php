<?php

use App\Http\Controllers\Api\v1\SearchAPIController;
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

Route::middleware('auth:sanctum')->group(function() {
    Route::get('statement/{statement:uuid}', [StatementAPIController::class,'show'])->name('api.v1.statement.show')->can('view statements');
    Route::get('statement/existing-puid/{puid}', [StatementAPIController::class,'existingPuid'])->name('api.v1.statement.existing-puid')->can('view statements');
    Route::post('statement', [StatementAPIController::class,'store'])->name('api.v1.statement.store')->can('create statements');
    Route::post('statements', [StatementAPIController::class,'storeMultiple'])->name('api.v1.statements.store')->can('create statements');

    Route::group(['middleware' => ['can:administrate']], static function(){
        Route::post('opensearch/search', [SearchAPIController::class, 'search'])->name('api.v1.opensearch.search');
        Route::post('opensearch/count', [SearchAPIController::class, 'count'])->name('api.v1.opensearch.count');
        Route::post('opensearch/sql', [SearchAPIController::class, 'sql'])->name('api.v1.opensearch.sql');
        Route::get('opensearch/aggregate/{date}', [SearchAPIController::class, 'aggregate'])->name('api.v1.opensearch.aggregate');
        Route::get('opensearch/platforms', [SearchAPIController::class, 'platforms'])->name('api.v1.opensearch.platforms');
    });
});

