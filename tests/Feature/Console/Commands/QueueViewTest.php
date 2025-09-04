<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueueViewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_runs_successfully_with_an_empty_queue(): void
    {
        $this->artisan('queue:view')
            ->expectsOutput('Queues:')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_runs_successfully_with_jobs_in_the_queue(): void
    {
        // Seed a job
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'MyTestJob']),
            'attempts' => 1,
            'reserved_at' => null,
            'available_at' => now()->unix(),
            'created_at' => now()->unix(),
        ]);

        // Just check that the command runs and contains some key output
        $this->artisan('queue:view')
            ->expectsOutputToContain('default') // The queue name
            ->expectsOutputToContain('MyTestJob') // The job name
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_handles_reserved_and_multi_attempt_jobs(): void
    {
        // Seed a reserved job with multiple attempts to hit all logic branches
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'MyReservedJob']),
            'attempts' => 5,
            'reserved_at' => now()->unix(),
            'available_at' => now()->unix(),
            'created_at' => now()->unix(),
        ]);

        // Just check that the command runs and contains some key output
        $this->artisan('queue:view')
            ->expectsOutputToContain('default')
            ->expectsOutputToContain('MyReservedJob')
            ->assertExitCode(0);
    }
}
