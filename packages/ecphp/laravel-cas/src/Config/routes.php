<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

use EcPhp\LaravelCas\Controllers\HomepageController as Homepage;
use EcPhp\LaravelCas\Controllers\LoginController as Login;
use EcPhp\LaravelCas\Controllers\LogoutController as Logout;
use EcPhp\LaravelCas\Controllers\ProxyCallbackController as ProxyCallback;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], static function () {
    Route::get('/homepage', Homepage::class)->name('laravel-cas-homepage');
    Route::get('/login', Login::class)->name('laravel-cas-login');
    Route::get('/logout', Logout::class)->name('laravel-cas-logout');
    Route::get('/proxy/callback', ProxyCallback::class)->name('laravel-cas-proxy-callback');
});
