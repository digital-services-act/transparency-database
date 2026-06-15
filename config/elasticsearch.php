<?php

$username = env('ES_ADDON_USER', null);
$password = env('ES_ADDON_PASSWORD', null);
$scheme = env('ES_ADDON_SCHEME', 'https');

$rawHosts = [];

$primaryHost = env('ES_ADDON_HOST');
if (is_string($primaryHost) && trim($primaryHost) !== '') {
    $rawHosts[] = $primaryHost;
}

$csvHosts = env('ES_ADDON_HOSTS');
if (is_string($csvHosts) && trim($csvHosts) !== '') {
    $rawHosts = array_merge($rawHosts, preg_split('/\s*,\s*/', $csvHosts) ?: []);
}

$useNumberedHosts = filter_var(env('ES_ADDON_USE_NUMBERED_HOSTS', false), FILTER_VALIDATE_BOOLEAN);
if ($useNumberedHosts) {
    for ($i = 1; $i <= 64; $i++) {
        $numberedHost = env('ES_ADDON_HOST_'.$i);

        if (is_string($numberedHost) && trim($numberedHost) !== '') {
            $rawHosts[] = $numberedHost;
        }
    }
}

$normalizeHost = static function (mixed $host) use ($scheme): ?array {
    if (! is_string($host)) {
        return null;
    }

    $host = trim($host);
    if ($host === '') {
        return null;
    }

    $hostScheme = rtrim((string) $scheme, ':/');

    if (preg_match('#^(https?)://(.+)$#i', $host, $matches)) {
        $hostScheme = strtolower($matches[1]);
        $host = $matches[2];
    }

    return [
        'host' => $host,
        'scheme' => $hostScheme,
    ];
};

$normalizedHosts = [];
foreach ($rawHosts as $rawHost) {
    $host = $normalizeHost($rawHost);

    if ($host === null) {
        continue;
    }

    $normalizedHosts[$host['scheme'].'://'.$host['host']] = $host;
}
$normalizedHosts = array_values($normalizedHosts);

$hosts = array_map(static fn (array $host): string => $host['host'], $normalizedHosts);

$uris = array_map(static function (array $host) use ($username, $password): string {
    $uri = $host['scheme'].'://';

    if (is_string($username) && $username !== '' && is_string($password) && $password !== '') {
        $uri .= rawurlencode($username).':'.rawurlencode($password).'@';
    }

    return $uri.$host['host'];
}, $normalizedHosts);

$bulkRetryDelaysMs = array_values(array_filter(
    array_map(
        static fn (string $delay): int => max(0, (int) trim($delay)),
        explode(',', (string) env('ES_ADDON_BULK_RETRY_DELAYS_MS', '250,750,1500,3000')),
    ),
    static fn (int $delay): bool => $delay > 0,
));

return [
    'enabled' => env('ELASTICSEARCH_ENABLED', null),
    'hosts' => $hosts,
    'uri' => $uris,
    'basicAuthentication' => [
        'username' => $username,
        'password' => $password,
    ],
    'apiKey' => env('ES_ADDON_API_KEY', null),
    'retries' => env('ES_ADDON_RETRIES', 2),
    'bulk_retry_delays_ms' => $bulkRetryDelaysMs,
];
