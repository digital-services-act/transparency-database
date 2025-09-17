<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ElasticSearchIndexAliasCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_creates_alias(): void
    {
        $result = [
            'index' => 'test_index',
            'alias' => 'test_alias',
            'created' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('createIndexAlias')
            ->with('test_index', 'test_alias')
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'test_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_creation(): void
    {
        $result = [
            'index' => 'test_index',
            'alias' => 'problematic_alias',
            'created' => true,
            'acknowledged' => false,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('createIndexAlias')
            ->with('test_index', 'problematic_alias')
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'test_index',
            'alias' => 'problematic_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('createIndexAlias')
            ->with('nonexistent_index', 'test_alias')
            ->andThrow(new RuntimeException('Index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'nonexistent_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_existing_alias(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('createIndexAlias')
            ->with('existing_index', 'existing_alias')
            ->andThrow(new RuntimeException('Alias already exists on this index'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'existing_index',
            'alias' => 'existing_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_runtime_exception(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('createIndexAlias')
            ->with('error_index', 'error_alias')
            ->andThrow(new RuntimeException('Connection timeout'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'error_index',
            'alias' => 'error_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_complex_index_and_alias_names(): void
    {
        $result = [
            'index' => 'statements_production_2024.09.17',
            'alias' => 'current-production-index',
            'created' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('createIndexAlias')
            ->with('statements_production_2024.09.17', 'current-production-index')
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'statements_production_2024.09.17',
            'alias' => 'current-production-index',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_multiple_alias_operations(): void
    {
        $result1 = [
            'index' => 'shared_index',
            'alias' => 'alias_one',
            'created' => true,
            'acknowledged' => true,
        ];

        $result2 = [
            'index' => 'shared_index',
            'alias' => 'alias_two',
            'created' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('createIndexAlias')
            ->with('shared_index', 'alias_one')
            ->andReturn($result1);
        $mockService->shouldReceive('createIndexAlias')
            ->with('shared_index', 'alias_two')
            ->andReturn($result2);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        // First alias
        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'shared_index',
            'alias' => 'alias_one',
        ])
            ->assertExitCode(0);

        // Second alias
        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'shared_index',
            'alias' => 'alias_two',
        ])
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
