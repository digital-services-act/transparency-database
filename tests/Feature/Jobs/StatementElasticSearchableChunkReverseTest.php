<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementElasticSearchableChunkReverse;
use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class StatementElasticSearchableChunkReverseTest extends TestCase
{
    use RefreshDatabase;

    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = Mockery::mock(StatementElasticSearchService::class);
        $this->app->instance(StatementElasticSearchService::class, $this->mockService);
    }

    public function test_job_processes_highest_chunk_and_dispatches_next_lower_job(): void
    {
        for ($i = 1899; $i <= 2000; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                $ids = $collection->pluck('id')->values()->toArray();

                return $collection->count() === 101
                    && $ids === range(2000, 1900)
                    && ! in_array(1899, $ids);
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 2000, 100);
        $job->handle($this->mockService);

        Queue::assertPushed(StatementElasticSearchableChunkReverse::class, function ($job) {
            return $job->min === 1001
                && $job->max === 1899
                && $job->chunk === 100
                && $job->range === true;
        });
    }

    public function test_retry_does_not_dispatch_next_lower_job(): void
    {
        for ($i = 1899; $i <= 2000; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                $ids = $collection->pluck('id')->values()->toArray();

                return $collection->count() === 101
                    && $ids === range(2000, 1900)
                    && ! in_array(1899, $ids);
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 2000, 100);
        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle($this->mockService);

        Queue::assertNotPushed(StatementElasticSearchableChunkReverse::class);
    }

    public function test_job_processes_final_lower_chunk_without_dispatching_next(): void
    {
        for ($i = 1001; $i <= 1050; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                $ids = $collection->pluck('id')->values()->toArray();

                return $collection->count() === 50 && $ids === range(1050, 1001);
            }));

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunkReverse Min Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 1050, 100);
        $job->handle($this->mockService);

        Queue::assertNotPushed(StatementElasticSearchableChunkReverse::class);
    }

    public function test_job_stops_when_emergency_stop_flag_is_set(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(true);

        $this->mockService->shouldNotReceive('bulkIndexStatements');

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 2000, 100);
        $job->handle($this->mockService);

        Queue::assertNotPushed(StatementElasticSearchableChunkReverse::class);
    }

    public function test_job_preserves_range_mode_when_dispatching_next_lower_job(): void
    {
        for ($i = 9399; $i <= 9500; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                $ids = $collection->pluck('id')->values()->toArray();

                return $collection->count() === 101
                    && $ids === range(9500, 9400)
                    && ! in_array(9399, $ids);
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(9001, 9500, 100, false);
        $job->handle($this->mockService);

        Queue::assertPushed(StatementElasticSearchableChunkReverse::class, function ($job) {
            return $job->min === 9001
                && $job->max === 9399
                && $job->chunk === 100
                && $job->range === false;
        });
    }

    public function test_range_logic_with_gaps_in_data(): void
    {
        $existingIds = [1991, 1993, 1995, 1997, 1999];
        foreach ($existingIds as $id) {
            Statement::factory()->create(['id' => $id]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) use ($existingIds) {
                $ids = $collection->pluck('id')->values()->toArray();

                return $collection->count() === 5 && $ids === array_reverse($existingIds);
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 2000, 20);
        $job->handle($this->mockService);

        Queue::assertPushed(StatementElasticSearchableChunkReverse::class, function ($job) {
            return $job->min === 1001
                && $job->max === 1979
                && $job->chunk === 20;
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
