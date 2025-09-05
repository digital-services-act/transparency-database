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
        // This should process IDs 10-15 and dispatch next job for 16-20
        $job = new PlatformPuidDeleteChunk(10, 20, 5);
        $job->handle();

        // Verify next job was dispatched with correct boundaries
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
        // This should process only remaining IDs 18-20, not beyond max
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
        // This should process all IDs 1-5, not attempt to go beyond max
        $job = new PlatformPuidDeleteChunk(1, 5, 10);
        $job->handle();

        // Verify no next job was dispatched
        Queue::assertNotPushed(PlatformPuidDeleteChunk::class);
    }

    /**
     * @test
     */
    public function it_respects_max_boundary_and_does_not_delete_beyond(): void
    {
        Queue::fake();
        Log::shouldReceive('info')->once()->with(\Mockery::pattern('/PlatformPuidDeleteChunk Max Reached at/'));

        // Test case: min=95, max=100, chunk=10
        // Should only delete [95,96,97,98,99,100] - exactly 6 records, not 10
        DB::shouldReceive('table')->with('platform_puids')->andReturnSelf();
        DB::shouldReceive('whereIn')->with('id', [95, 96, 97, 98, 99, 100])->andReturnSelf();
        DB::shouldReceive('delete')->once();

        $job = new PlatformPuidDeleteChunk(95, 100, 10);
        $job->handle();

        // Verify no additional job is dispatched since we've reached max
        Queue::assertNotPushed(PlatformPuidDeleteChunk::class);
    }

    /**
     * @test
     */
    public function it_ensures_complete_coverage_of_range(): void
    {
        Queue::fake();

        // Test the full sequence to ensure complete coverage
        // First chunk: min=1, max=10, chunk=3 should delete [1,2,3,4] and dispatch next with min=5
        DB::shouldReceive('table')->with('platform_puids')->andReturnSelf();
        DB::shouldReceive('whereIn')->with('id', [1, 2, 3, 4])->andReturnSelf();
        DB::shouldReceive('delete')->once();

        $job = new PlatformPuidDeleteChunk(1, 10, 3);
        $job->handle();

        // Verify next job starts where this one left off
        Queue::assertPushed(PlatformPuidDeleteChunk::class, function ($job) {
            return $job->min === 5 && $job->max === 10 && $job->chunk === 3;
        });
    }
}
