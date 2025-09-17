<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ElasticSearchIndexAliasSwapTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_swaps_alias(): void
    {
        $result = [
            'from_index' => 'old_index',
            'to_index' => 'new_index',
            'alias' => 'current',
            'swapped' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('swapIndexAlias')
            ->with('old_index', 'new_index', 'current')
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'old_index',
            'target' => 'new_index',
            'alias' => 'current',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_swap(): void
    {
        $result = [
            'from_index' => 'source_index',
            'to_index' => 'target_index',
            'alias' => 'production',
            'swapped' => true,
            'acknowledged' => false,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('swapIndexAlias')
            ->with('source_index', 'target_index', 'production')
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'source_index',
            'target' => 'target_index',
            'alias' => 'production',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_source_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('swapIndexAlias')
            ->with('nonexistent_index', 'target_index', 'test_alias')
            ->andThrow(new RuntimeException('Source index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'nonexistent_index',
            'target' => 'target_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_target_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('swapIndexAlias')
            ->with('source_index', 'nonexistent_target', 'test_alias')
            ->andThrow(new RuntimeException('Target index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'source_index',
            'target' => 'nonexistent_target',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_alias_not_on_source(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('swapIndexAlias')
            ->with('source_index', 'target_index', 'missing_alias')
            ->andThrow(new RuntimeException('Alias does not exist on source index'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'source_index',
            'target' => 'target_index',
            'alias' => 'missing_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_alias_already_on_target(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('swapIndexAlias')
            ->with('source_index', 'target_index', 'existing_alias')
            ->andThrow(new RuntimeException('Alias already exists on target index'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'source_index',
            'target' => 'target_index',
            'alias' => 'existing_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_exception(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('swapIndexAlias')
            ->with('error_index', 'target_index', 'error_alias')
            ->andThrow(new RuntimeException('Connection timeout'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'error_index',
            'target' => 'target_index',
            'alias' => 'error_alias',
        ])
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
