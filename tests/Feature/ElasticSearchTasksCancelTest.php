<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ElasticSearchTasksCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_cancels_tasks(): void
    {
        $cancelResult = [
            'cancelled_tasks' => 3,
            'acknowledged' => true,
            'response' => ['nodes' => []],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('cancelAllTasks')
            ->andReturn($cancelResult);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_no_cancellable_tasks(): void
    {
        $cancelResult = [
            'cancelled_tasks' => 0,
            'acknowledged' => true,
            'response' => ['nodes' => []],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('cancelAllTasks')
            ->andReturn($cancelResult);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_cancellation(): void
    {
        $cancelResult = [
            'cancelled_tasks' => 2,
            'acknowledged' => false,
            'response' => ['nodes' => []],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('cancelAllTasks')
            ->andReturn($cancelResult);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_large_number_of_cancelled_tasks(): void
    {
        $cancelResult = [
            'cancelled_tasks' => 50,
            'acknowledged' => true,
            'response' => ['nodes' => []],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('cancelAllTasks')
            ->andReturn($cancelResult);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_exception(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('cancelAllTasks')
            ->andThrow(new \Exception('Connection timeout'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_elasticsearch_error(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('cancelAllTasks')
            ->andThrow(new \Exception('Cluster unavailable'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_single_task_cancellation(): void
    {
        $cancelResult = [
            'cancelled_tasks' => 1,
            'acknowledged' => true,
            'response' => ['nodes' => []],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('cancelAllTasks')
            ->andReturn($cancelResult);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
