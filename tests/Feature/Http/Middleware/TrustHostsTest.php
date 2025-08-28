<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\TrustHosts;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TrustHostsTest extends TestCase
{
    protected bool $seed = false;

    protected bool $runMigrations = false;

    private TrustHosts $middleware;

    #[\Override]
    protected function setUp(): void
    {
        $this->app = $this->createApplication();
        $this->middleware = new TrustHosts($this->app);
    }

    /** @test */
    public function it_returns_array_with_application_url_pattern()
    {
        // Set a test application URL
        Config::set('app.url', 'https://example.com');

        $hosts = $this->middleware->hosts();

        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertEquals('^(.+\.)?example\.com$', $hosts[0]);
    }

    /** @test */
    public function it_handles_application_url_with_subdomain()
    {
        // Set a test application URL with subdomain
        Config::set('app.url', 'https://api.example.com');

        $hosts = $this->middleware->hosts();

        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertEquals('^(.+\.)?api\.example\.com$', $hosts[0]);
    }

    /** @test */
    public function it_handles_application_url_with_port()
    {
        // Set a test application URL with port
        Config::set('app.url', 'https://example.com:8080');

        $hosts = $this->middleware->hosts();

        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertEquals('^(.+\.)?example\.com$', $hosts[0]);
    }

    /** @test */
    public function it_handles_application_url_with_path()
    {
        // Set a test application URL with path
        Config::set('app.url', 'https://example.com/path');

        $hosts = $this->middleware->hosts();

        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertEquals('^(.+\.)?example\.com$', $hosts[0]);
    }

    /** @test */
    public function it_handles_localhost()
    {
        // Set localhost as application URL
        Config::set('app.url', 'http://localhost');

        $hosts = $this->middleware->hosts();

        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertEquals('^(.+\.)?localhost$', $hosts[0]);
    }

    /** @test */
    public function it_handles_ip_address()
    {
        // Set IP address as application URL
        Config::set('app.url', 'http://127.0.0.1');

        $hosts = $this->middleware->hosts();

        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertEquals('^(.+\.)?127\.0\.0\.1$', $hosts[0]);
    }

    /** @test */
    public function it_handles_empty_application_url()
    {
        // Set empty application URL
        Config::set('app.url', '');

        $hosts = $this->middleware->hosts();

        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertNull($hosts[0]);
    }

    /** @test */
    public function it_handles_invalid_application_url()
    {
        // Set invalid URL
        Config::set('app.url', 'not-a-url');

        $hosts = $this->middleware->hosts();

        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertNull($hosts[0]);
    }
}
