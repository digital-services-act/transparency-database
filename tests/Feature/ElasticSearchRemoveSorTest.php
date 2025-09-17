<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ElasticSearchRemoveSorTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_removes_document(): void
    {
        $result = [
            'index' => 'test_index',
            'document_id' => 12345,
            'deleted' => true,
            'result' => 'deleted',
            'version' => 2,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('removeDocumentFromIndex')
            ->with('test_index', 12345)
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '12345',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_document_removal_without_version(): void
    {
        $result = [
            'index' => 'test_index',
            'document_id' => 67890,
            'deleted' => true,
            'result' => 'deleted',
            'version' => null,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('removeDocumentFromIndex')
            ->with('test_index', 67890)
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '67890',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('removeDocumentFromIndex')
            ->with('nonexistent_index', 12345)
            ->andThrow(new RuntimeException('Index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'nonexistent_index',
            'id' => '12345',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_invalid_document_id(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('removeDocumentFromIndex')
            ->with('test_index', 0)
            ->andThrow(new RuntimeException('Invalid document ID'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '0',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_document_not_found(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('removeDocumentFromIndex')
            ->with('test_index', 99999)
            ->andThrow(new RuntimeException('Document not found in index'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '99999',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_exception(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('removeDocumentFromIndex')
            ->with('error_index', 12345)
            ->andThrow(new RuntimeException('Connection timeout'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'error_index',
            'id' => '12345',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_negative_document_id(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('removeDocumentFromIndex')
            ->with('test_index', -1)
            ->andThrow(new RuntimeException('Invalid document ID'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '-1',
        ])
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
