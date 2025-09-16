<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ElasticSearchTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_displays_tasks_with_cancellable_tasks(): void
    {
        $tasksInfo = [
            'total_tasks' => 5,
            'cancellable_tasks' => 2,
            'cancellable' => [
                [
                    'id' => 'task_1',
                    'node' => 'node_1',
                    'type' => 'reindex',
                    'action' => 'indices:data/write/reindex',
                    'description' => 'Reindexing from source_index to target_index',
                    'start_time' => 1632835200000,
                    'running_time' => 50000000,
                    'cancellable' => true,
                ],
                [
                    'id' => 'task_2',
                    'node' => 'node_2',
                    'type' => 'bulk',
                    'action' => 'indices:data/write/bulk',
                    'description' => 'Bulk indexing operation on large dataset with many documents',
                    'start_time' => 1632835210000,
                    'running_time' => 75000000,
                    'cancellable' => true,
                ],
            ],
            'all_tasks' => [
                // This would include all tasks, but we only need cancellable for this test
            ],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getTasks')
            ->andReturn($tasksInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_displays_no_cancellable_tasks(): void
    {
        $tasksInfo = [
            'total_tasks' => 3,
            'cancellable_tasks' => 0,
            'cancellable' => [],
            'all_tasks' => [
                [
                    'id' => 'task_1',
                    'node' => 'node_1',
                    'type' => 'search',
                    'action' => 'indices:data/read/search',
                    'description' => 'Simple search operation',
                    'start_time' => 1632835200000,
                    'running_time' => 10000000,
                    'cancellable' => false,
                ],
            ],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getTasks')
            ->andReturn($tasksInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_handles_empty_task_list(): void
    {
        $tasksInfo = [
            'total_tasks' => 0,
            'cancellable_tasks' => 0,
            'cancellable' => [],
            'all_tasks' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getTasks')
            ->andReturn($tasksInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_handles_large_number_of_cancellable_tasks(): void
    {
        $cancellableTasks = [];
        for ($i = 1; $i <= 10; $i++) {
            $cancellableTasks[] = [
                'id' => "task_{$i}",
                'node' => "node_{$i}",
                'type' => 'reindex',
                'action' => 'indices:data/write/reindex',
                'description' => "Long running reindex operation number {$i}",
                'start_time' => 1632835200000 + ($i * 1000),
                'running_time' => 100000000 + ($i * 10000000),
                'cancellable' => true,
            ];
        }

        $tasksInfo = [
            'total_tasks' => 15,
            'cancellable_tasks' => 10,
            'cancellable' => $cancellableTasks,
            'all_tasks' => $cancellableTasks, // Simplified for test
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getTasks')
            ->andReturn($tasksInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_handles_exception(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getTasks')
            ->andThrow(new \Exception('Connection timeout'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_handles_tasks_with_long_descriptions(): void
    {
        $tasksInfo = [
            'total_tasks' => 1,
            'cancellable_tasks' => 1,
            'cancellable' => [
                [
                    'id' => 'task_1',
                    'node' => 'node_1',
                    'type' => 'reindex',
                    'action' => 'indices:data/write/reindex',
                    'description' => 'This is a very long description that should be truncated when displayed in the table to prevent table formatting issues and improve readability for users',
                    'start_time' => 1632835200000,
                    'running_time' => 50000000,
                    'cancellable' => true,
                ],
            ],
            'all_tasks' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getTasks')
            ->andReturn($tasksInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
