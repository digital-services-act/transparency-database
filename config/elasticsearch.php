<?php

return [
    'hosts' => [env('ES_ADDON_HOST', 'localhost:9200').':9200'],
    'uri' => [env('ES_ADDON_URI', 'https://localhost:9200')],
    'basicAuthentication' => [
        'username' => env('ES_ADDON_USER', 'admin'),
        'password' => env('ES_ADDON_PASSWORD', 'admin'),
    ],
    'apiKey' => env('ES_ADDON_API_KEY', null),
    'retries' => env('ES_ADDON_RETRIES', 2),
];
