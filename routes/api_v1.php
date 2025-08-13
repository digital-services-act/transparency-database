<?php

use App\Http\Controllers\Api\v1\ElasticSearchAPIController;
use App\Http\Controllers\Api\v1\PlatformAPIController;
use App\Http\Controllers\Api\v1\PlatformUserAPIController;
use App\Http\Controllers\Api\v1\StatementAPIController;
use App\Http\Controllers\Api\v1\StatementMultipleAPIController;
use App\Http\Controllers\Api\v1\UserAPIController;
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
    

    Route::group(['prefix' => 'elastic', 'middleware' => ['can:administrate']], static function () {
        Route::get('indices', [ElasticSearchAPIController::class, 'indices'])->name('api.v1.elasticsearch.indices');
        Route::post('search', [ElasticSearchAPIController::class, 'search'])->name('api.v1.elasticsearch.search');
        Route::post('count', [ElasticSearchAPIController::class, 'count'])->name('api.v1.elasticsearch.count');
        Route::post('sql', [ElasticSearchAPIController::class, 'sql'])->name('api.v1.elasticsearch.sql');
        Route::post('lucene', [ElasticSearchAPIController::class, 'lucene'])->name('api.v1.elasticsearch.lucene');
        Route::post('cacheclear', [ElasticSearchAPIController::class, 'clearAggregateCache'])->name('api.v1.elasticsearch.cacheclear');
        Route::get('aggregates/{date}/{attributes?}', [ElasticSearchAPIController::class, 'aggregatesForDate'])->name('api.v1.elasticsearch.aggregates.date');
        Route::get('aggregates-csv/{date}', [ElasticSearchAPIController::class, 'aggregatesCsvForDate'])->name('api.v1.elasticsearch.aggregates.csv.date');
        Route::get('aggregates/{start}/{end}/{attributes?}', [ElasticSearchAPIController::class, 'aggregatesForRange'])->name('api.v1.elasticsearch.aggregates.range');
        Route::get('aggregatesd/{start}/{end}/{attributes?}', [ElasticSearchAPIController::class, 'aggregatesForRangeDates'])->name('api.v1.elasticsearch.aggregates.range.dates');
        Route::get('platforms', [ElasticSearchAPIController::class, 'platforms'])->name('api.v1.elasticsearch.platforms');
        Route::get('labels', [ElasticSearchAPIController::class, 'labels'])->name('api.v1.elasticsearch.labels');
        Route::get('total', [ElasticSearchAPIController::class, 'total'])->name('api.v1.elasticsearch.total');
        Route::get('datetotal/{date}', [ElasticSearchAPIController::class, 'dateTotal'])->name('api.v1.elasticsearch.datetotal');
        Route::get('platformdatetotal/{platform_id}/{date}', [ElasticSearchAPIController::class, 'platformDateTotal'])->name('api.v1.elasticsearch.platformdatetotal');
        Route::get('datetotalrange/{start}/{end}', [ElasticSearchAPIController::class, 'dateTotalRange'])->name('api.v1.elasticsearch.datetotalrange');
        Route::get('datetotalsrange/{start}/{end}', [ElasticSearchAPIController::class, 'dateTotalsRange'])->name('api.v1.elasticsearch.datetotalsrange');
    });

    //Onboarding routes
    Route::get('platform/{platform:dsa_common_id}', static fn(\App\Models\Platform $platform) => (new PlatformAPIController())->get($platform))->name('api.v1.platform.get')->can('view platforms');
    Route::put('platform/{platform:dsa_common_id}', static fn(\App\Models\Platform $platform, \App\Http\Requests\PlatformUpdateRequest $request): \Illuminate\Http\JsonResponse => (new PlatformAPIController())->update($platform, $request))->name('api.v1.platform.update')->can('create platforms');
    Route::post('platform', static fn(\App\Http\Requests\PlatformStoreRequest $request): \Illuminate\Http\JsonResponse => (new PlatformAPIController())->store($request))->name('api.v1.platform.store')->can('create platforms');
    Route::get('user/{email}', static fn($email) => (new UserAPIController())->get($email))->name('api.v1.user.get')->can('view users');
    Route::delete('user/{email}', static fn($email) => (new UserAPIController())->delete($email))->name('api.v1.user.delete')->can('create users');
    Route::post('platform/{platform:dsa_common_id}/users', static fn(\App\Http\Requests\PlatformUsersStoreRequest $request, \App\Models\Platform $platform): \Illuminate\Http\JsonResponse => (new PlatformUserAPIController())->store($request, $platform))->name('api.v1.platform-users.store')->can('create users');

});