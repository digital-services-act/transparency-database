<?php

use App\Http\Controllers\Api\v1\OpenSearchAPIController;
use App\Http\Controllers\Api\v1\PlatformAPIController;
use App\Http\Controllers\Api\v1\PlatformUserAPIController;
use App\Http\Controllers\Api\v1\StatementAPIController;
use App\Http\Controllers\Api\v1\StatementMultipleAPIController;
use App\Http\Controllers\Api\v1\UserAPIController;
use App\Http\Controllers\PlatformController;
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

Route::middleware('auth:sanctum')->group(static function () {
    Route::get('statement/{statement}', [StatementAPIController::class, 'show'])->name('api.v1.statement.show')->can('view statements');
    Route::get('statement/uuid/{uuid}', [StatementAPIController::class, 'showUuid'])->name('api.v1.statement.show.uuid')->can('view statements');
    Route::get('statement/existing-puid/{puid}', [StatementAPIController::class, 'existingPuid'])->name('api.v1.statement.existing-puid')->can('view statements');
    Route::post('statement', [StatementAPIController::class, 'store'])->name('api.v1.statement.store')->can('create statements');
    Route::post('statements', [StatementMultipleAPIController::class, 'store'])->name('api.v1.statements.store')->can('create statements');
    Route::group(['middleware' => ['can:administrate']], static function () {
        Route::post('opensearch/search', [OpenSearchAPIController::class, 'search'])->name('api.v1.opensearch.search');
        Route::post('opensearch/count', [OpenSearchAPIController::class, 'count'])->name('api.v1.opensearch.count');
        Route::post('opensearch/sql', [OpenSearchAPIController::class, 'sql'])->name('api.v1.opensearch.sql');
        Route::post('opensearch/explain', [OpenSearchAPIController::class, 'explain'])->name('api.v1.opensearch.explain');
        Route::post('opensearch/cacheclear', [OpenSearchAPIController::class, 'clearAggregateCache'])->name('api.v1.opensearch.cacheclear');
        Route::get('opensearch/aggregates/{date}/{attributes?}', [OpenSearchAPIController::class, 'aggregatesForDate'])->name('api.v1.opensearch.aggregates.date');
        Route::get('opensearch/aggregates-csv/{date}', [OpenSearchAPIController::class, 'aggregatesCsvForDate'])->name('api.v1.opensearch.aggregates.csv.date');
        Route::get('opensearch/aggregates/{start}/{end}/{attributes?}', [OpenSearchAPIController::class, 'aggregatesForRange'])->name('api.v1.opensearch.aggregates.range');
        Route::get('opensearch/aggregatesd/{start}/{end}/{attributes?}', [OpenSearchAPIController::class, 'aggregatesForRangeDates'])->name('api.v1.opensearch.aggregates.range.dates');
        Route::get('opensearch/platforms', [OpenSearchAPIController::class, 'platforms'])->name('api.v1.opensearch.platforms');
        Route::get('opensearch/labels', [OpenSearchAPIController::class, 'labels'])->name('api.v1.opensearch.labels');
        Route::get('opensearch/total', [OpenSearchAPIController::class, 'total'])->name('api.v1.opensearch.total');
        Route::get('opensearch/datetotal/{date}', [OpenSearchAPIController::class, 'dateTotal'])->name('api.v1.opensearch.datetotal');
        Route::get('opensearch/platformdatetotal/{platform_id}/{date}', [OpenSearchAPIController::class, 'platformDateTotal'])->name('api.v1.opensearch.platformdatetotal');
        Route::get('opensearch/datetotalrange/{start}/{end}', [OpenSearchAPIController::class, 'dateTotalRange'])->name('api.v1.opensearch.datetotalrange');
        Route::get('opensearch/datetotalsrange/{start}/{end}', [OpenSearchAPIController::class, 'dateTotalsRange'])->name('api.v1.opensearch.datetotalsrange');
    });
    //Onboarding routes
    Route::get('platform/{platform:dsa_common_id}', [PlatformAPIController::class, 'get'])->name('api.v1.platform.get')->can('view platforms');
    Route::put('platform/{platform:dsa_common_id}', [PlatformAPIController::class, 'update'])->name('api.v1.platform.update')->can('create platforms');
    Route::post('platform', [PlatformAPIController::class, 'store'])->name('api.v1.platform.store')->can('create platforms');
    Route::get('user/{email}', [UserAPIController::class, 'get'])->name('api.v1.user.get')->can('view users');
    Route::post('platform/{platform:dsa_common_id}/users', [PlatformUserAPIController::class, 'store'])->name('api.v1.platform-users.store')->can('create users');
});

