<?php

use App\Http\Controllers\Api\v1\OpenSearchAPIController;
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
        Route::post('opensearch/search', [OpenSearchAPIController::class, 'search'])->name('api.v1.opensearch.search');
        Route::post('opensearch/count', [OpenSearchAPIController::class, 'count'])->name('api.v1.opensearch.count');
        Route::post('opensearch/sql', [OpenSearchAPIController::class, 'sql'])->name('api.v1.opensearch.sql');
        Route::post('opensearch/explain', [OpenSearchAPIController::class, 'explain'])->name('api.v1.opensearch.explain');
        Route::get('opensearch/aggregates/{date}/{attributes?}', [OpenSearchAPIController::class, 'aggregatesForDate'])->name('api.v1.opensearch.aggregates.date');
        Route::get('opensearch/aggregates/{start}/{end}/{attributes?}', [OpenSearchAPIController::class, 'aggregatesForRange'])->name('api.v1.opensearch.aggregates.range');
        Route::get('opensearch/platforms', [OpenSearchAPIController::class, 'platforms'])->name('api.v1.opensearch.platforms');
        Route::get('opensearch/labels', [OpenSearchAPIController::class, 'labels'])->name('api.v1.opensearch.labels');
    });
});

