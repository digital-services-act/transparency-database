<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

return [
    'base_url' => env('CAS_URL', 'https://webgate.ec.europa.eu/cas'),
    'redirect_login_route' => env('CAS_REDIRECT_LOGIN_ROUTE', 'laravel-cas-homepage'),
    'protocol' => [
        'login' => [
            'path' => '/login',
            'allowed_parameters' => [
                0 => 'service',
                1 => 'renew',
                2 => 'gateway',
            ],
            'default_parameters' => [
                'service' => env('CAS_REDIRECT_LOGIN_URL', 'https://my-app/homepage-login'),
            ],
        ],
        'serviceValidate' => [
            'path' => '/p3/serviceValidate',
            'allowed_parameters' => [
                0 => 'format',
                1 => 'pgtUrl',
                2 => 'service',
                3 => 'ticket',
            ],
            'default_parameters' => [
                'format' => 'JSON',
                // 'pgtUrl' => 'https://my-app/casProxyCallback',
            ],
        ],
        'logout' => [
            'path' => '/logout',
            'allowed_parameters' => [
                0 => 'service',
            ],
            'default_parameters' => [
                'service' => env('CAS_REDIRECT_LOGOUT_URL', 'https://my-app/homepage-logout'),
            ],
        ],
        'proxy' => [
            'path' => '/proxy',
            'allowed_parameters' => [
                0 => 'targetService',
                1 => 'pgt',
            ],
        ],
        'proxyValidate' => [
            'path' => '/proxyValidate',
            'allowed_parameters' => [
                0 => 'format',
                1 => 'pgtUrl',
                2 => 'service',
                3 => 'ticket',
            ],
            'default_parameters' => [
                'format' => 'JSON',
                'pgtUrl' => 'https://my-app/casProxyCallback',
            ],
        ],
    ],
];
