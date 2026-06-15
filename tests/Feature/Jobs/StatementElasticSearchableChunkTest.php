<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementElasticSearchableChunk;
use App\Models\Statement;
use App\Services\StatementElasticIndexerService;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class StatementElasticSearchableChunkTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_processes_chunk_and_dispatches_next_job(): void
    {
        // Create simple test statements
        for ($i = 1001; $i <= 1005; $i++) {
            Statement::factory()->create(['id' => $i]);
        }
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 100);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, [1001, 1002, 1003, 1004, 1005]);

        // Should dispatch next job since 1101 < 2000
        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 1102 && $job->max === 2000 && $job->chunk === 100;
        });
    }

    public function test_retry_does_not_dispatch_next_job(): void
    {
        for ($i = 1001; $i <= 1005; $i++) {
            Statement::factory()->create(['id' => $i]);
        }
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 100);
        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, [1001, 1002, 1003, 1004, 1005]);

        Queue::assertNotPushed(StatementElasticSearchableChunk::class);
    }

    public function test_job_processes_final_chunk_without_dispatching_next(): void
    {
        // Create test statements for final chunk
        for ($i = 1998; $i <= 2000; $i++) {
            Statement::factory()->create(['id' => $i]);
        }
        $elastic = ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1998, 2000, 100);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, [1998, 1999, 2000]);

        // Should NOT dispatch next job since we reached max
        Queue::assertNotPushed(StatementElasticSearchableChunk::class);
    }

    public function test_job_handles_chunk_that_exceeds_max(): void
    {
        // Create statements where chunk would exceed max
        for ($i = 1996; $i <= 2000; $i++) {
            Statement::factory()->create(['id' => $i]);
        }
        $elastic = ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        // Chunk of 100 starting at 1996 would go to 2096, but max is 2000
        $job = new StatementElasticSearchableChunk(1996, 2000, 100);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, [1996, 1997, 1998, 1999, 2000]);

        // Should not dispatch next job since we reached max
        Queue::assertNotPushed(StatementElasticSearchableChunk::class);
    }

    public function test_job_handles_small_chunk_size(): void
    {
        // Create test statements for small chunk
        for ($i = 1001; $i <= 1002; $i++) {
            Statement::factory()->create(['id' => $i]);
        }
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 2);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, [1001, 1002]);

        // Should dispatch next job with correct next min (1001 + 2 + 1 = 1004)
        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 1004 && $job->max === 2000 && $job->chunk === 2;
        });
    }

    public function test_job_handles_empty_statement_range(): void
    {
        // No statements in this range
        $elastic = ElasticMocker::fake();

        Queue::fake();

        $job = new StatementElasticSearchableChunk(9001, 9500, 100);
        $job->handle($this->indexer());

        // Should still dispatch next job even with empty results
        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 9102 && $job->max === 9500 && $job->chunk === 100;
        });
        $this->assertCount(0, $elastic->requests());
    }

    public function test_job_preserves_range_mode_when_dispatching_next_job(): void
    {
        $elastic = ElasticMocker::fake();

        Queue::fake();

        $job = new StatementElasticSearchableChunk(9001, 9500, 100, false);
        $job->handle($this->indexer());

        Queue::assertPushed(StatementElasticSearchableChunk::class, function ($job) {
            return $job->min === 9102
                && $job->max === 9500
                && $job->chunk === 100
                && $job->range === false;
        });
        $this->assertCount(0, $elastic->requests());
    }

    public function test_job_handles_single_item_chunk(): void
    {
        // Single statement
        Statement::factory()->create(['id' => 1500]);
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1500, 2000, 1);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, [1500]);

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
        $elastic = ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(703, 784, 100);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, range(703, 784));

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
        $elastic = ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(750, 780, 50);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, range(750, 780));

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
        $elastic = ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunk Max Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1000, 1099, 100);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, range(1000, 1099));

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
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunk(500, 1000, 20);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, range(500, 520));

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
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 20);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, $existingIds);

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

    private function indexer(): StatementElasticIndexerService
    {
        return app(StatementElasticIndexerService::class);
    }

    private function assertBulkIds(ElasticMocker $elastic, array $expectedIds): void
    {
        $this->assertCount(1, $elastic->requests());
        $request = $elastic->requests()[0];

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/_bulk', $request->getUri()->getPath());
        $this->assertSame($expectedIds, $this->bulkIdsFromPayload((string) $request->getBody()));
    }

    private function bulkIdsFromPayload(string $payload): array
    {
        preg_match_all('/"_id":(\d+)/', $payload, $matches);

        return array_map('intval', $matches[1]);
    }
}
