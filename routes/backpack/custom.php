<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('platform', 'PlatformCrudController');
    Route::get('charts/top-platforms', 'Charts\TopPlatformsChartController@response')->name('charts.top-platforms.index');
    Route::get('charts/daily-statements', 'Charts\DailyStatementsChartController@response')->name('charts.daily-statements.index');
    Route::get('charts/top-categories', 'Charts\TopCategoriesChartController@response')->name('charts.top-categories.index');
}); // this should be the absolute last line of this file