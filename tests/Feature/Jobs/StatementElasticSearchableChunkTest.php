<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementElasticSearchableChunk;
use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class StatementElasticSearchableChunkTest extends TestCase
{
    use RefreshDatabase;

    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = Mockery::mock(StatementElasticSearchService::class);
        $this->app->instance(StatementElasticSearchService::class, $this->mockService);
    }

    public function test_job_processes_chunk_and_dispatches_next_job(): void
    {
        // Create simple test statements
        for ($i = 1001; $i <= 1005; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                return $collection->count() === 5 &&
                       $collection->pluck('id')->sort()->values()->toArray() === [1001, 1002, 1003, 1004, 1005];
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 100);
        $job->handle($this->mockService);

        // Should dispatch next job since 1101 < 2000
        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 1102 && $job->max === 2000 && $job->chunk === 100;
        });
    }

    public function test_job_processes_final_chunk_without_dispatching_next(): void
    {
        // Create test statements for final chunk
        for ($i = 1998; $i <= 2000; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                return $collection->count() === 3;
            }));

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1998, 2000, 100);
        $job->handle($this->mockService);

        // Should NOT dispatch next job since we reached max
        Queue::assertNotPushed(StatementElasticSearchableChunk::class);
    }

    public function test_job_stops_when_emergency_stop_flag_is_set(): void
    {
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(true);

        // Should not call bulk indexing when stopped
        $this->mockService->shouldNotReceive('bulkIndexStatements');

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 100);
        $job->handle($this->mockService);

        // Should not dispatch any jobs when stopped
        Queue::assertNotPushed(StatementElasticSearchableChunk::class);
    }

    public function test_job_handles_chunk_that_exceeds_max(): void
    {
        // Create statements where chunk would exceed max
        for ($i = 1996; $i <= 2000; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                return $collection->count() === 5;
            }));

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        // Chunk of 100 starting at 1996 would go to 2096, but max is 2000
        $job = new StatementElasticSearchableChunk(1996, 2000, 100);
        $job->handle($this->mockService);

        // Should not dispatch next job since we reached max
        Queue::assertNotPushed(StatementElasticSearchableChunk::class);
    }

    public function test_job_handles_small_chunk_size(): void
    {
        // Create test statements for small chunk
        for ($i = 1001; $i <= 1002; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                return $collection->count() === 2;
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 2);
        $job->handle($this->mockService);

        // Should dispatch next job with correct next min (1001 + 2 + 1 = 1004)
        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 1004 && $job->max === 2000 && $job->chunk === 2;
        });
    }

    public function test_job_handles_empty_statement_range(): void
    {
        // No statements in this range
        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                return $collection->count() === 0;
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(9001, 9500, 100);
        $job->handle($this->mockService);

        // Should still dispatch next job even with empty results
        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 9102 && $job->max === 9500 && $job->chunk === 100;
        });
    }

    public function test_job_handles_single_item_chunk(): void
    {
        // Single statement
        Statement::factory()->create(['id' => 1500]);

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                return $collection->count() === 1 && $collection->first()->id === 1500;
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1500, 2000, 1);
        $job->handle($this->mockService);

        // Should dispatch next job
        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 1502 && $job->max === 2000 && $job->chunk === 1;
        });
    }

    public function test_range_boundaries_are_precise_example_703_to_784(): void
    {
        // Test your specific example: min=703, max=784, chunk=100
        // Should process exactly 703-784 (82 IDs), not 785, not stopping at 783
        for ($i = 700; $i <= 790; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                $ids = $collection->pluck('id')->sort()->values()->toArray();
                $expectedRange = range(703, 784); // Should be exactly 703-784

                return $collection->count() === 82 && // Exactly 82 items (703 to 784 inclusive)
                       $ids === $expectedRange && // Contains exactly the expected range
                       min($ids) === 703 && // Starts at 703
                       max($ids) === 784 && // Ends at 784
                       ! in_array(702, $ids) && // Doesn't include 702
                       ! in_array(785, $ids); // Doesn't include 785
            }));

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(703, 784, 100);
        $job->handle($this->mockService);

        // Should NOT dispatch next job since we reached max (784)
        Queue::assertNotPushed(StatementElasticSearchableChunk::class);
    }

    public function test_range_boundaries_with_chunk_that_would_exceed_max(): void
    {
        // Test: min=750, max=780, chunk=50
        // end = min + chunk = 750 + 50 = 800, but max is 780
        // So should only process 750-780 (31 IDs), not 781-800
        for ($i = 745; $i <= 785; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                $ids = $collection->pluck('id')->sort()->values()->toArray();
                $expectedRange = range(750, 780); // Should be exactly 750-780

                return $collection->count() === 31 && // Exactly 31 items (750 to 780 inclusive)
                       $ids === $expectedRange && // Contains exactly the expected range
                       min($ids) === 750 && // Starts at 750
                       max($ids) === 780 && // Ends at 780 (max), not 800 (min+chunk)
                       ! in_array(749, $ids) && // Doesn't include 749
                       ! in_array(781, $ids); // Doesn't include 781
            }));

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(750, 780, 50);
        $job->handle($this->mockService);

        // Should NOT dispatch next job since we reached max
        Queue::assertNotPushed(StatementElasticSearchableChunk::class);
    }

    public function test_range_boundaries_with_exact_chunk_fit(): void
    {
        // Test: min=1000, max=1099, chunk=100
        // end = min + chunk = 1000 + 100 = 1100, but max is 1099
        // So should process exactly 1000-1099 (100 IDs)
        for ($i = 995; $i <= 1105; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                $ids = $collection->pluck('id')->sort()->values()->toArray();
                $expectedRange = range(1000, 1099); // Should be exactly 1000-1099

                return $collection->count() === 100 && // Exactly 100 items
                       $ids === $expectedRange && // Contains exactly the expected range
                       min($ids) === 1000 && // Starts at 1000
                       max($ids) === 1099 && // Ends at 1099 (max)
                       ! in_array(999, $ids) && // Doesn't include 999
                       ! in_array(1100, $ids); // Doesn't include 1100
            }));

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1000, 1099, 100);
        $job->handle($this->mockService);

        // Should NOT dispatch next job since we reached max
        Queue::assertNotPushed(StatementElasticSearchableChunk::class);
    }

    public function test_range_boundaries_with_next_job_dispatch(): void
    {
        // Test: min=500, max=1000, chunk=20
        // Should process 500-520 (21 IDs) and dispatch next job starting at 521
        for ($i = 495; $i <= 525; $i++) {
            Statement::factory()->create(['id' => $i]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) {
                $ids = $collection->pluck('id')->sort()->values()->toArray();
                $expectedRange = range(500, 520); // Should be exactly 500-520

                return $collection->count() === 21 && // Exactly 21 items (500 to 520 inclusive)
                       $ids === $expectedRange && // Contains exactly the expected range
                       min($ids) === 500 && // Starts at 500
                       max($ids) === 520 && // Ends at 520 (min + chunk)
                       ! in_array(499, $ids) && // Doesn't include 499
                       ! in_array(521, $ids); // Doesn't include 521 (reserved for next job)
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(500, 1000, 20);
        $job->handle($this->mockService);

        // Should dispatch next job starting at exactly 521 (min + chunk + 1)
        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 521 && $job->max === 1000 && $job->chunk === 20;
        });
    }

    public function test_range_logic_with_gaps_in_data(): void
    {
        // Test that range logic works even when there are gaps in actual data
        // Create statements: 1001, 1003, 1005, 1007, 1009 (missing 1002, 1004, 1006, 1008, 1010)
        // Range should still be 1001-1020, but only existing statements should be processed
        $existingIds = [1001, 1003, 1005, 1007, 1009];
        foreach ($existingIds as $id) {
            Statement::factory()->create(['id' => $id]);
        }

        Cache::shouldReceive('get')
            ->with('stop_reindexing', false)
            ->andReturn(false);

        $this->mockService->shouldReceive('bulkIndexStatements')
            ->once()
            ->with(Mockery::on(function ($collection) use ($existingIds) {
                $ids = $collection->pluck('id')->sort()->values()->toArray();

                return $collection->count() === 5 && // Only 5 existing statements
                       $ids === $existingIds && // Contains exactly the existing IDs
                       min($ids) === 1001 && // Starts at first existing ID
                       max($ids) === 1009; // Ends at last existing ID in range
            }));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 20);
        $job->handle($this->mockService);

        // Should still dispatch next job at 1022 (min + chunk + 1) regardless of data gaps
        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 1022 && $job->max === 2000 && $job->chunk === 20;
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
