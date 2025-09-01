<?php

return [
    'hosts' => [env('ES_ADDON_HOST', 'localhost:9200').':9200'],
    'uri' => [env('ES_ADDON_URI', null)],
    'basicAuthentication' => [
        'username' => env('ES_ADDON_USER', null),
        'password' => env('ES_ADDON_PASSWORD', null),
    ],
    'apiKey' => env('ES_ADDON_API_KEY', null),
    'retries' => env('ES_ADDON_RETRIES', 2),
];
