<?php

$host = env('ES_ADDON_HOST');
$username = env('ES_ADDON_USER', null);
$password = env('ES_ADDON_PASSWORD', null);
$scheme = env('ES_ADDON_SCHEME', 'https');

if (is_string($host)) {
    $host = trim($host);
}

if (! is_string($host) || $host === '') {
    $host = null;
} elseif (preg_match('#^(https?)://(.+)$#i', $host, $matches)) {
    $scheme = $matches[1];
    $host = $matches[2];
}

$uri = null;

if ($host !== null) {
    $scheme = rtrim((string) $scheme, ':/');

    if (is_string($username) && $username !== '' && is_string($password) && $password !== '') {
        $uri = $scheme.'://'.rawurlencode($username).':'.rawurlencode($password).'@'.$host;
    } else {
        $uri = $scheme.'://'.$host;
    }
}

return [
    'hosts' => [$host],
    'uri' => [$uri],
    'basicAuthentication' => [
        'username' => $username,
        'password' => $password,
    ],
    'apiKey' => env('ES_ADDON_API_KEY', null),
    'retries' => env('ES_ADDON_RETRIES', 2),
];
