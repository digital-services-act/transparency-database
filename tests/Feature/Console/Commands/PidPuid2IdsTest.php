<?php

namespace Tests\Feature\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Symfony\Component\VarDumper\VarDumper;
use Tests\TestCase;

class PidPuid2IdsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_runs_without_errors(): void
    {
        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('PlatformIdPuidToIds')
            ->once()
            ->with(123, 'test-puid-456')
            ->andReturn(['id1', 'id2', 'id3']);

        // Bind the mock to the container
        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Mock VarDumper::dump
        VarDumper::setHandler(function ($var) {
            // Just capture that dump was called with the expected data
            $this->assertEquals(['id1', 'id2', 'id3'], $var);
        });

        // Run the command
        $this->artisan('pidpuid2ids', ['platform_id' => '123', 'puid' => 'test-puid-456'])
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_handles_string_platform_id_conversion(): void
    {
        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('PlatformIdPuidToIds')
            ->once()
            ->with(999, 'another-puid')
            ->andReturn(['result1']);

        // Bind the mock to the container
        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Mock VarDumper::dump
        VarDumper::setHandler(function ($var) {
            $this->assertEquals(['result1'], $var);
        });

        // Run the command with string platform_id that should be converted to int
        $this->artisan('pidpuid2ids', ['platform_id' => '999', 'puid' => 'another-puid'])
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_handles_empty_result(): void
    {
        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('PlatformIdPuidToIds')
            ->once()
            ->with(456, 'empty-puid')
            ->andReturn([]);

        // Bind the mock to the container
        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Mock VarDumper::dump
        VarDumper::setHandler(function ($var) {
            $this->assertEquals([], $var);
        });

        // Run the command
        $this->artisan('pidpuid2ids', ['platform_id' => '456', 'puid' => 'empty-puid'])
            ->assertExitCode(0);
    }
}