<?php

$host = env('ES_ADDON_HOST');
$scheme = env('ES_ADDON_SCHEME', 'https');

if (is_string($host)) {
    $host = trim($host);
}

if (! is_string($host) || $host === '') {
    $host = null;
} elseif (! preg_match('#^https?://#i', $host)) {
    $host = rtrim((string) $scheme, ':/').'://'.$host;
}

return [
    'hosts' => [$host],
    'uri' => [$host],
    'basicAuthentication' => [
        'username' => env('ES_ADDON_USER', null),
        'password' => env('ES_ADDON_PASSWORD', null),
    ],
    'apiKey' => env('ES_ADDON_API_KEY', null),
    'retries' => env('ES_ADDON_RETRIES', 2),
];
