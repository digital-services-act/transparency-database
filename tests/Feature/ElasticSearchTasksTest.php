<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_displays_tasks_with_cancellable_tasks(): void
    {
        ElasticMocker::fake()->tasksReturn([
            'task_1' => [
                'type' => 'reindex',
                'action' => 'indices:data/write/reindex',
                'description' => 'Reindexing from source_index to target_index',
                'start_time' => 1632835200000,
                'running_time' => 50000000,
                'cancellable' => true,
            ],
            'task_2' => [
                'type' => 'bulk',
                'action' => 'indices:data/write/bulk',
                'description' => 'Bulk indexing operation on large dataset with many documents',
                'start_time' => 1632835210000,
                'running_time' => 75000000,
                'cancellable' => true,
            ],
            'task_3' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
            'task_4' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
            'task_5' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
        ]);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_displays_no_cancellable_tasks(): void
    {
        ElasticMocker::fake()->tasksReturn([
            'task_1' => [
                'type' => 'search',
                'action' => 'indices:data/read/search',
                'description' => 'Simple search operation',
                'start_time' => 1632835200000,
                'running_time' => 10000000,
                'cancellable' => false,
            ],
            'task_2' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
            'task_3' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
        ]);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_handles_empty_task_list(): void
    {
        ElasticMocker::fake()->tasksReturn([]);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_handles_large_number_of_cancellable_tasks(): void
    {
        $cancellableTasks = [];
        for ($i = 1; $i <= 10; $i++) {
            $cancellableTasks["task_{$i}"] = [
                'type' => 'reindex',
                'action' => 'indices:data/write/reindex',
                'description' => "Long running reindex operation number {$i}",
                'start_time' => 1632835200000 + ($i * 1000),
                'running_time' => 100000000 + ($i * 10000000),
                'cancellable' => true,
            ];
        }

        ElasticMocker::fake()->tasksReturn($cancellableTasks + [
            'task_11' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
            'task_12' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
            'task_13' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
            'task_14' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
            'task_15' => ['type' => 'search', 'action' => 'indices:data/read/search', 'cancellable' => false],
        ]);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_handles_exception(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timeout'));

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }

    public function test_command_handles_tasks_with_long_descriptions(): void
    {
        ElasticMocker::fake()->tasksReturn([
            'task_1' => [
                'type' => 'reindex',
                'action' => 'indices:data/write/reindex',
                'description' => 'This is a very long description that should be truncated when displayed in the table to prevent table formatting issues and improve readability for users',
                'start_time' => 1632835200000,
                'running_time' => 50000000,
                'cancellable' => true,
            ],
        ]);

        $this->artisan('elasticsearch:tasks')
            ->assertExitCode(0);
    }
}
