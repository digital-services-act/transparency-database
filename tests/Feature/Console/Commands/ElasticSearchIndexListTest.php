<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\ElasticSearchIndexList;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ElasticSearchIndexListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_can_be_instantiated(): void
    {
        $command = new ElasticSearchIndexList;

        $this->assertInstanceOf(ElasticSearchIndexList::class, $command);
        $this->assertEquals('elasticsearch:index-list', $command->getName());
        $this->assertEquals('Get some info about the elasticsearch.', $command->getDescription());
    }

    /**
     * @test
     */
    public function it_lists_elasticsearch_indices_with_dependency_injection(): void
    {
        // Now with business logic in the service, testing is much simpler!

        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('getIndexList')
            ->once()
            ->andReturn([
                'statements-2025-09-16',
                'statements-2025-09-15',
                'test-index',
            ]);

        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Run the command and verify it shows the expected table
        $this->artisan('elasticsearch:index-list')
            ->expectsTable(['Indexes'], [
                ['statements-2025-09-16'],
                ['statements-2025-09-15'],
                ['test-index'],
            ])
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_handles_empty_index_list_gracefully(): void
    {
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('getIndexList')
            ->once()
            ->andReturn([]); // No indices

        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Should show empty table but not crash
        $this->artisan('elasticsearch:index-list')
            ->expectsTable(['Indexes'], [])
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_handles_single_index(): void
    {
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('getIndexList')
            ->once()
            ->andReturn(['single-index']);

        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        $this->artisan('elasticsearch:index-list')
            ->expectsTable(['Indexes'], [
                ['single-index'],
            ])
            ->assertExitCode(0);
    }
}
