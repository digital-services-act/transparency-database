<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ElasticSearchIndexAliasDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_deletes_alias(): void
    {
        $result = [
            'index' => 'test_index',
            'alias' => 'test_alias',
            'deleted' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndexAlias')
            ->with('test_index', 'test_alias')
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'test_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_deletion(): void
    {
        $result = [
            'index' => 'test_index',
            'alias' => 'problematic_alias',
            'deleted' => true,
            'acknowledged' => false,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndexAlias')
            ->with('test_index', 'problematic_alias')
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'test_index',
            'alias' => 'problematic_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndexAlias')
            ->with('nonexistent_index', 'test_alias')
            ->andThrow(new RuntimeException('Index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'nonexistent_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_alias(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndexAlias')
            ->with('existing_index', 'nonexistent_alias')
            ->andThrow(new RuntimeException('Alias does not exist on this index'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'existing_index',
            'alias' => 'nonexistent_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_runtime_exception(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndexAlias')
            ->with('error_index', 'error_alias')
            ->andThrow(new RuntimeException('Connection timeout'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'error_index',
            'alias' => 'error_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_complex_index_and_alias_names(): void
    {
        $result = [
            'index' => 'statements_production_2024.09.17',
            'alias' => 'old-production-index',
            'deleted' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndexAlias')
            ->with('statements_production_2024.09.17', 'old-production-index')
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'statements_production_2024.09.17',
            'alias' => 'old-production-index',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_cleanup_multiple_aliases(): void
    {
        $result1 = [
            'index' => 'shared_index',
            'alias' => 'old_alias_one',
            'deleted' => true,
            'acknowledged' => true,
        ];

        $result2 = [
            'index' => 'shared_index',
            'alias' => 'old_alias_two',
            'deleted' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('deleteIndexAlias')
            ->with('shared_index', 'old_alias_one')
            ->andReturn($result1);
        $mockService->shouldReceive('deleteIndexAlias')
            ->with('shared_index', 'old_alias_two')
            ->andReturn($result2);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        // Delete first alias
        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'shared_index',
            'alias' => 'old_alias_one',
        ])
            ->assertExitCode(0);

        // Delete second alias
        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'shared_index',
            'alias' => 'old_alias_two',
        ])
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
