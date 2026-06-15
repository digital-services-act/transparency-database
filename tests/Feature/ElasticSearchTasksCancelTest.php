<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchTasksCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_cancels_tasks(): void
    {
        ElasticMocker::fake()
            ->tasksReturn([
                'task_1' => ['cancellable' => true],
                'task_2' => ['cancellable' => true],
                'task_3' => ['cancellable' => true],
            ])
            ->tasksCancelReturns();

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_no_cancellable_tasks(): void
    {
        ElasticMocker::fake()
            ->tasksReturn([
                'task_1' => ['cancellable' => false],
            ])
            ->tasksCancelReturns();

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_cancel_response_with_node_failures(): void
    {
        ElasticMocker::fake()
            ->tasksReturn([
                'task_1' => ['cancellable' => true],
                'task_2' => ['cancellable' => true],
            ])
            ->tasksCancelReturns([
                'nodes' => [],
                'node_failures' => [
                    ['reason' => ['reason' => 'task already completed']],
                ],
            ]);

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_large_number_of_cancelled_tasks(): void
    {
        $tasks = [];
        for ($i = 1; $i <= 50; $i++) {
            $tasks["task_{$i}"] = ['cancellable' => true];
        }

        ElasticMocker::fake()
            ->tasksReturn($tasks)
            ->tasksCancelReturns();

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_exception(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timeout'));

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_elasticsearch_error(): void
    {
        ElasticMocker::fake()
            ->tasksReturn(['task_1' => ['cancellable' => true]])
            ->exception(new RuntimeException('Cluster unavailable'));

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }

    public function test_command_handles_single_task_cancellation(): void
    {
        ElasticMocker::fake()
            ->tasksReturn(['task_1' => ['cancellable' => true]])
            ->tasksCancelReturns();

        $this->artisan('elasticsearch:tasks-cancel')
            ->assertExitCode(0);
    }
}
