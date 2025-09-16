<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ElasticSearchIndexDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_deletes_existing_index(): void
    {
        $deleteResult = [
            'index' => 'test_index',
            'deleted' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndex')
            ->with('test_index')
            ->andReturn($deleteResult);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-delete', ['index' => 'test_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_deletion(): void
    {
        $deleteResult = [
            'index' => 'problematic_index',
            'deleted' => true,
            'acknowledged' => false,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndex')
            ->with('problematic_index')
            ->andReturn($deleteResult);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-delete', ['index' => 'problematic_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndex')
            ->with('nonexistent_index')
            ->andThrow(new RuntimeException('Index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-delete', ['index' => 'nonexistent_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_runtime_exception(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndex')
            ->with('error_index')
            ->andThrow(new RuntimeException('Connection timeout'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-delete', ['index' => 'error_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_large_index_deletion(): void
    {
        $deleteResult = [
            'index' => 'large_production_index',
            'deleted' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndex')
            ->with('large_production_index')
            ->andReturn($deleteResult);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-delete', ['index' => 'large_production_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_index_with_special_characters(): void
    {
        $deleteResult = [
            'index' => 'test-index_2024.01',
            'deleted' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndex')
            ->with('test-index_2024.01')
            ->andReturn($deleteResult);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-delete', ['index' => 'test-index_2024.01'])
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
