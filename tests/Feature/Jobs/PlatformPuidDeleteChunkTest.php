<?php

namespace Tests\Feature\Jobs;

use App\Jobs\PlatformPuidDeleteChunk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PlatformPuidDeleteChunkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_deletes_chunk_and_dispatches_next_job(): void
    {
        // Mock the facades
        Queue::fake();
        DB::shouldReceive('table')->with('platform_puids')->andReturnSelf();
        DB::shouldReceive('whereIn')->with('id', [10, 11, 12, 13, 14, 15])->andReturnSelf();
        DB::shouldReceive('delete')->once();

        // Create job: min=10, max=20, chunk=5
        // This should process IDs 10-14 and dispatch next job for 16-20
        $job = new PlatformPuidDeleteChunk(10, 20, 5);
        $job->handle();

        // Verify next job was dispatched
        Queue::assertPushed(PlatformPuidDeleteChunk::class, function ($job) {
            return $job->min === 16 && $job->max === 20 && $job->chunk === 5;
        });
    }

    /**
     * @test
     */
    public function it_deletes_final_chunk_and_logs_completion(): void
    {
        // Mock the facades
        Queue::fake();
        DB::shouldReceive('table')->with('platform_puids')->andReturnSelf();
        DB::shouldReceive('whereIn')->with('id', [18, 19, 20])->andReturnSelf();
        DB::shouldReceive('delete')->once();
        Log::shouldReceive('info')->once()->with(\Mockery::pattern('/PlatformPuidDeleteChunk Max Reached at/'));

        // Create job: min=18, max=20, chunk=5
        // This should process IDs 18-20 (final chunk) and log completion
        $job = new PlatformPuidDeleteChunk(18, 20, 5);
        $job->handle();

        // Verify no next job was dispatched
        Queue::assertNotPushed(PlatformPuidDeleteChunk::class);
    }

    /**
     * @test
     */
    public function it_handles_single_chunk_scenario(): void
    {
        // Mock the facades
        Queue::fake();
        DB::shouldReceive('table')->with('platform_puids')->andReturnSelf();
        DB::shouldReceive('whereIn')->with('id', [1, 2, 3, 4, 5])->andReturnSelf();
        DB::shouldReceive('delete')->once();
        Log::shouldReceive('info')->once()->with(\Mockery::pattern('/PlatformPuidDeleteChunk Max Reached at/'));

        // Create job: min=1, max=5, chunk=10
        // This should process all IDs 1-5 in one chunk
        $job = new PlatformPuidDeleteChunk(1, 5, 10);
        $job->handle();

        // Verify no next job was dispatched
        Queue::assertNotPushed(PlatformPuidDeleteChunk::class);
    }
}
