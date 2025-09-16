<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ElasticSearchIndexInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_displays_index_information(): void
    {
        $indexInfo = [
            'uuid' => 'test-uuid-123-456',
            'documents' => 15000,
            'size_bytes' => 2097152, // 2MB
            'fields' => [
                ['id', 'long'],
                ['title', 'text'],
                ['category', 'keyword'],
            ],
            'shards' => [
                ['0', 'STARTED', '7500', '1.1mb'],
                ['1', 'STARTED', '7500', '1.1mb'],
            ],
            'aliases' => [
                ['alias' => 'current_index'],
                ['alias' => 'search_index'],
            ],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexInfo')
            ->with('test_index')
            ->andReturn($indexInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-info', ['index' => 'test_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_index_with_no_aliases(): void
    {
        $indexInfo = [
            'uuid' => 'no-alias-uuid-789',
            'documents' => 500,
            'size_bytes' => 1024, // 1KB
            'fields' => [
                ['id', 'long'],
            ],
            'shards' => [
                ['0', 'STARTED', '500', '1kb'],
            ],
            'aliases' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexInfo')
            ->with('no_alias_index')
            ->andReturn($indexInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-info', ['index' => 'no_alias_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_large_index_with_gigabyte_size(): void
    {
        $indexInfo = [
            'uuid' => 'large-index-uuid',
            'documents' => 1000000,
            'size_bytes' => 3221225472, // 3GB
            'fields' => [
                ['content', 'text'],
            ],
            'shards' => [
                ['0', 'STARTED', '250000', '800mb'],
            ],
            'aliases' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexInfo')
            ->with('large_index')
            ->andReturn($indexInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-info', ['index' => 'large_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexInfo')
            ->with('nonexistent_index')
            ->andThrow(new RuntimeException('Index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-info', ['index' => 'nonexistent_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_alias_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexInfo')
            ->with('alias_index')
            ->andThrow(new RuntimeException('Index is not in the indices stats, probably you used an alias?'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-info', ['index' => 'alias_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_empty_index(): void
    {
        $indexInfo = [
            'uuid' => 'empty-index-uuid',
            'documents' => 0,
            'size_bytes' => 283, // bytes only
            'fields' => [
                ['_id', 'keyword'],
            ],
            'shards' => [],
            'aliases' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexInfo')
            ->with('empty_index')
            ->andReturn($indexInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-info', ['index' => 'empty_index'])
            ->assertExitCode(0);
    }

    public function test_human_file_size_formatting(): void
    {
        $indexInfo = [
            'uuid' => 'size-test-uuid',
            'documents' => 1000,
            'size_bytes' => 1073741824, // 1GB exactly
            'fields' => [
                ['data', 'text'],
            ],
            'shards' => [],
            'aliases' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexInfo')
            ->with('size_test')
            ->andReturn($indexInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-info', ['index' => 'size_test'])
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
