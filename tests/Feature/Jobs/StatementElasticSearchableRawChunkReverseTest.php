<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementElasticSearchableRawChunkReverse;
use App\Services\StatementElasticSearchService;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class StatementElasticSearchableRawChunkReverseTest extends TestCase
{
    use RefreshDatabase;

    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = Mockery::mock(StatementElasticSearchService::class);
        $this->app->instance(StatementElasticSearchService::class, $this->mockService);
    }

    public function test_job_indexes_highest_raw_chunk_and_dispatches_next_lower_job(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexRawStatementsForIdRange')
            ->once()
            ->with(1900, 2000, true, 'desc');

        Queue::fake();

        $job = new StatementElasticSearchableRawChunkReverse(1001, 2000, 100);
        $job->handle($this->mockService);

        Queue::assertPushed(StatementElasticSearchableRawChunkReverse::class, function ($job) {
            return $job->min === 1001
                && $job->max === 1899
                && $job->chunk === 100
                && $job->range === true;
        });
    }

    public function test_retry_indexes_current_chunk_but_does_not_dispatch_next_lower_job(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexRawStatementsForIdRange')
            ->once()
            ->with(1900, 2000, true, 'desc');

        Queue::fake();

        $job = new StatementElasticSearchableRawChunkReverse(1001, 2000, 100);
        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle($this->mockService);

        Queue::assertNotPushed(StatementElasticSearchableRawChunkReverse::class);
    }

    public function test_job_stops_when_emergency_stop_flag_is_set(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(true);

        $this->mockService->shouldNotReceive('bulkIndexRawStatementsForIdRange');

        Queue::fake();

        $job = new StatementElasticSearchableRawChunkReverse(1001, 2000, 100);
        $job->handle($this->mockService);

        Queue::assertNotPushed(StatementElasticSearchableRawChunkReverse::class);
    }

    public function test_job_processes_final_lower_chunk_without_dispatching_next(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexRawStatementsForIdRange')
            ->once()
            ->with(1001, 1050, true, 'desc');

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableRawChunkReverse Min Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableRawChunkReverse(1001, 1050, 100);
        $job->handle($this->mockService);

        Queue::assertNotPushed(StatementElasticSearchableRawChunkReverse::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
