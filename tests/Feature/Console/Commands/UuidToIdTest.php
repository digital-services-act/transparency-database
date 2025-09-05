<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\UuidToId;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class UuidToIdTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_runs_without_errors(): void
    {
        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('uuidToId')
            ->once()
            ->with('test-uuid-123')
            ->andReturn('12345');

        // Bind the mock to the container
        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Run the command
        $this->artisan('uuid2id', ['uuid' => 'test-uuid-123'])
            ->expectsOutput('ID: 12345')
            ->assertExitCode(0);
    }
}