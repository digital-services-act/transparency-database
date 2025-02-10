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
    Route::group(['prefix'=>'opensearch','middleware' => ['can:administrate']], static function () {
        Route::post('search', [OpenSearchAPIController::class, 'search'])->name('api.v1.opensearch.search');
        Route::post('count', [OpenSearchAPIController::class, 'count'])->name('api.v1.opensearch.count');
        Route::post('sql', [OpenSearchAPIController::class, 'sql'])->name('api.v1.opensearch.sql');
        Route::post('explain', [OpenSearchAPIController::class, 'explain'])->name('api.v1.opensearch.explain');
        Route::post('cacheclear', [OpenSearchAPIController::class, 'clearAggregateCache'])->name('api.v1.opensearch.cacheclear');
        Route::get('aggregates/{date}/{attributes?}', [OpenSearchAPIController::class, 'aggregatesForDate'])->name('api.v1.opensearch.aggregates.date');
        Route::get('aggregates-csv/{date}', [OpenSearchAPIController::class, 'aggregatesCsvForDate'])->name('api.v1.opensearch.aggregates.csv.date');
        Route::get('aggregates/{start}/{end}/{attributes?}', [OpenSearchAPIController::class, 'aggregatesForRange'])->name('api.v1.opensearch.aggregates.range');
        Route::get('aggregatesd/{start}/{end}/{attributes?}', [OpenSearchAPIController::class, 'aggregatesForRangeDates'])->name('api.v1.opensearch.aggregates.range.dates');
        Route::get('platforms', [OpenSearchAPIController::class, 'platforms'])->name('api.v1.opensearch.platforms');
        Route::get('labels', [OpenSearchAPIController::class, 'labels'])->name('api.v1.opensearch.labels');
        Route::get('total', [OpenSearchAPIController::class, 'total'])->name('api.v1.opensearch.total');
        Route::get('datetotal/{date}', [OpenSearchAPIController::class, 'dateTotal'])->name('api.v1.opensearch.datetotal');
        Route::get('platformdatetotal/{platform_id}/{date}', [OpenSearchAPIController::class, 'platformDateTotal'])->name('api.v1.opensearch.platformdatetotal');
        Route::get('datetotalrange/{start}/{end}', [OpenSearchAPIController::class, 'dateTotalRange'])->name('api.v1.opensearch.datetotalrange');
        Route::get('datetotalsrange/{start}/{end}', [OpenSearchAPIController::class, 'dateTotalsRange'])->name('api.v1.opensearch.datetotalsrange');
    });

    Route::group(['prefix'=>'research','middleware' => ['can:research API']], static function () {
        Route::post('search', [OpenSearchAPIController::class, 'search']);
        Route::post('count', [OpenSearchAPIController::class, 'count']);
        Route::post('sql', [OpenSearchAPIController::class, 'sql']);
        Route::get('aggregates/{date}/{attributes?}', [OpenSearchAPIController::class, 'aggregatesForDate']);
        Route::get('platforms', [OpenSearchAPIController::class, 'platforms']);
        Route::get('labels', [OpenSearchAPIController::class, 'labels']);
    });
    //Onboarding routes
    Route::get('platform/{platform:dsa_common_id}', static fn(\App\Models\Platform $platform) => (new \App\Http\Controllers\Api\v1\PlatformAPIController())->get($platform))->name('api.v1.platform.get')->can('view platforms');
    Route::put('platform/{platform:dsa_common_id}', static fn(\App\Models\Platform $platform, \App\Http\Requests\PlatformUpdateRequest $request): \Illuminate\Http\JsonResponse => (new \App\Http\Controllers\Api\v1\PlatformAPIController())->update($platform, $request))->name('api.v1.platform.update')->can('create platforms');
    Route::post('platform', static fn(\App\Http\Requests\PlatformStoreRequest $request): \Illuminate\Http\JsonResponse => (new \App\Http\Controllers\Api\v1\PlatformAPIController())->store($request))->name('api.v1.platform.store')->can('create platforms');
    Route::get('user/{email}', static fn($email) => (new \App\Http\Controllers\Api\v1\UserAPIController())->get($email))->name('api.v1.user.get')->can('view users');
    Route::delete('user/{email}', static fn($email) => (new \App\Http\Controllers\Api\v1\UserAPIController())->delete($email))->name('api.v1.user.delete')->can('create users');
    Route::post('platform/{platform:dsa_common_id}/users', static fn(\App\Http\Requests\PlatformUsersStoreRequest $request, \App\Models\Platform $platform): \Illuminate\Http\JsonResponse => (new \App\Http\Controllers\Api\v1\PlatformUserAPIController())->store($request, $platform))->name('api.v1.platform-users.store')->can('create users');

});

