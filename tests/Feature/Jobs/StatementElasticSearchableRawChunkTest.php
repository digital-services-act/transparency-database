<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementElasticSearchableRawChunk;
use App\Services\StatementElasticIndexerService;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class StatementElasticSearchableRawChunkTest extends TestCase
{
    use RefreshDatabase;

    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = Mockery::mock(StatementElasticIndexerService::class);
        $this->app->instance(StatementElasticIndexerService::class, $this->mockService);
    }

    public function test_job_indexes_raw_chunk_and_dispatches_next_job(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexRawStatementsForIdRange')
            ->once()
            ->with(1001, 1101, true);

        Queue::fake();

        $job = new StatementElasticSearchableRawChunk(1001, 2000, 100);
        $job->handle($this->mockService);

        Queue::assertPushed(StatementElasticSearchableRawChunk::class, function ($job) {
            return $job->min === 1102
                && $job->max === 2000
                && $job->chunk === 100
                && $job->range === true;
        });
    }

    public function test_retry_indexes_current_chunk_but_does_not_dispatch_next_job(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexRawStatementsForIdRange')
            ->once()
            ->with(1001, 1101, true);

        Queue::fake();

        $job = new StatementElasticSearchableRawChunk(1001, 2000, 100);
        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle($this->mockService);

        Queue::assertNotPushed(StatementElasticSearchableRawChunk::class);
    }

    public function test_job_stops_when_emergency_stop_flag_is_set(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(true);

        $this->mockService->shouldNotReceive('bulkIndexRawStatementsForIdRange');

        Queue::fake();

        $job = new StatementElasticSearchableRawChunk(1001, 2000, 100);
        $job->handle($this->mockService);

        Queue::assertNotPushed(StatementElasticSearchableRawChunk::class);
    }

    public function test_job_processes_final_chunk_without_dispatching_next(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexRawStatementsForIdRange')
            ->once()
            ->with(1998, 2000, true);

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableRawChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableRawChunk(1998, 2000, 100);
        $job->handle($this->mockService);

        Queue::assertNotPushed(StatementElasticSearchableRawChunk::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
