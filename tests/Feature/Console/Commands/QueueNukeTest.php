<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueueNukeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_queue_tables_in_batches_and_restarts_queue(): void
    {
        foreach (range(1, 3) as $id) {
            DB::table('jobs')->insert([
                'id' => $id,
                'queue' => 'default',
                'payload' => 'test',
                'attempts' => 0,
                'reserved_at' => null,
                'available_at' => now()->unix(),
                'created_at' => now()->unix(),
            ]);

            DB::table('failed_jobs')->insert([
                'id' => $id,
                'uuid' => 'test-uuid-'.$id,
                'connection' => 'database',
                'queue' => 'default',
                'payload' => 'test',
                'exception' => 'test',
                'failed_at' => now(),
            ]);

            DB::table('job_batches')->insert([
                'id' => 'test-batch-id-'.$id,
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
        }

        $this->assertDatabaseCount('jobs', 3);
        $this->assertDatabaseCount('failed_jobs', 3);
        $this->assertDatabaseCount('job_batches', 3);

        $this->artisan('queue:nuke', ['--batch' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
        $this->assertDatabaseCount('job_batches', 0);
        $this->assertTrue(Cache::has('illuminate:queue:restart'));
    }
}
