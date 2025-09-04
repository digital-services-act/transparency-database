<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\QueueNuke;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\TestCase;

class QueueNukeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_truncates_queue_tables_and_restarts_queue(): void
    {
        // Seed the queue tables with some dummy data
        DB::table('jobs')->insert([
            'id' => 1,
            'queue' => 'default',
            'payload' => 'test',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->unix(),
            'created_at' => now()->unix(),
        ]);
        DB::table('failed_jobs')->insert([
            'id' => 1,
            'uuid' => 'test-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => 'test',
            'exception' => 'test',
            'failed_at' => now(),
        ]);
        DB::table('job_batches')->insert([
            'id' => 'test-batch-id',
            'name' => 'test batch',
            'total_jobs' => 1,
            'pending_jobs' => 1,
            'failed_jobs' => 0,
            'failed_job_ids' => '[]',
            'options' => null,
            'created_at' => now()->unix(),
            'cancelled_at' => null,
            'finished_at' => null,
        ]);

        // Assert that the tables have data before we run the command
        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseCount('failed_jobs', 1);
        $this->assertDatabaseCount('job_batches', 1);

        // Create a partial mock of the command
        $command = $this->partialMock(QueueNuke::class, function (MockInterface $mock) {
            // Mock the 'call' method to ensure it's called with 'queue:restart'
            $mock->shouldReceive('call')->once()->with('queue:restart');
        });

        // Manually call the handle method
        app()->call([$command, 'handle']);

        // Assert that the tables are now empty
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
        $this->assertDatabaseCount('job_batches', 0);
    }
}
