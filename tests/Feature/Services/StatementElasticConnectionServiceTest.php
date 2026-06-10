<?php

namespace Tests\Feature\Services;

use App\Services\StatementElasticConnectionService;
use Elastic\Elasticsearch\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StatementElasticConnectionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_throws_when_elasticsearch_is_not_configured(): void
    {
        config([
            'elasticsearch.uri' => [],
            'elasticsearch.basicAuthentication.username' => null,
            'elasticsearch.basicAuthentication.password' => null,
        ]);

        $service = new StatementElasticConnectionService;

        $this->assertFalse($service->isConfigured());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Elasticsearch is not configured.');

        $service->client();
    }

    public function test_it_builds_a_configured_client_with_basic_authentication(): void
    {
        config([
            'elasticsearch.uri' => ['http://localhost:9200'],
            'elasticsearch.basicAuthentication.username' => 'elastic',
            'elasticsearch.basicAuthentication.password' => 'secret',
            'elasticsearch.retries' => 0,
        ]);

        $service = new StatementElasticConnectionService;

        $this->assertTrue($service->isConfigured());
        $this->assertInstanceOf(Client::class, $service->client());
    }

    public function test_it_rebuilds_client_after_configuration_changes(): void
    {
        config(['elasticsearch.uri' => []]);

        $service = new StatementElasticConnectionService;

        $this->assertFalse($service->isConfigured());

        config(['elasticsearch.uri' => ['http://localhost:9200']]);

        $service->rebuildClient();

        $this->assertTrue($service->isConfigured());
    }

    public function test_configured_uri_detection_accepts_string_config_values(): void
    {
        config(['elasticsearch.uri' => ' http://localhost:9200 ']);

        $this->assertTrue(StatementElasticConnectionService::hasConfiguredUris());
    }
}
