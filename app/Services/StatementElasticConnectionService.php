<?php

namespace App\Services;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use RuntimeException;

class StatementElasticConnectionService
{
    private string $index_name = 'statement_index';

    private ?Client $client = null;

    public function __construct()
    {
        $this->client = $this->makeClient();
    }

    private function makeClient(): ?Client
    {
        if (! self::isEnabledByConfig()) {
            return null;
        }

        $hosts = $this->configuredHosts();

        if ($hosts === []) {
            return null;
        }

        $builder = ClientBuilder::create()
            ->setHosts($hosts)
            ->setRetries((int) config('elasticsearch.retries', 2));

        $username = config('elasticsearch.basicAuthentication.username');
        $password = config('elasticsearch.basicAuthentication.password');

        if (is_string($username) && $username !== '' && is_string($password) && $password !== '') {
            $builder->setBasicAuthentication($username, $password);
        }

        return $builder->build();
    }

    public function client(): Client
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Elasticsearch is disabled or not configured. Set ELASTICSEARCH_ENABLED=true and ES_ADDON_HOST, or leave ELASTICSEARCH_ENABLED unset and configure ES_ADDON_HOST.');
        }

        if ($this->client === null) {
            $this->client = $this->makeClient();
        }

        return $this->client;
    }

    public function rebuildClient(): void
    {
        $this->client = $this->makeClient();
    }

    public function statementIndexName(): string
    {
        return $this->index_name;
    }

    public function isConfigured(): bool
    {
        return self::isEnabledByConfig() && self::hasConfiguredUris();
    }

    public static function isEnabledByConfig(): bool
    {
        $enabled = config('elasticsearch.enabled');

        if ($enabled === null || $enabled === '') {
            return self::hasConfiguredUris();
        }

        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }

    public static function hasConfiguredUris(): bool
    {
        return self::configuredUris() !== [];
    }

    private function configuredHosts(): array
    {
        return self::configuredUris();
    }

    private static function configuredUris(): array
    {
        $hosts = config('elasticsearch.uri', []);

        if (! is_array($hosts)) {
            $hosts = [$hosts];
        }

        return array_values(array_filter(array_map(static function ($host): ?string {
            if (! is_string($host)) {
                return null;
            }

            $host = trim($host);

            return $host === '' ? null : $host;
        }, $hosts)));
    }
}
