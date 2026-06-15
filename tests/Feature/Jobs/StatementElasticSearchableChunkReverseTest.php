<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementElasticSearchableChunkReverse;
use App\Models\Statement;
use App\Services\StatementElasticIndexerService;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class StatementElasticSearchableChunkReverseTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_processes_highest_chunk_and_dispatches_next_lower_job(): void
    {
        for ($i = 1899; $i <= 2000; $i++) {
            Statement::factory()->create(['id' => $i]);
        }
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 2000, 100);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, range(2000, 1900));

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
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 2000, 100);
        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, range(2000, 1900));

        Queue::assertNotPushed(StatementElasticSearchableChunkReverse::class);
    }

    public function test_job_processes_final_lower_chunk_without_dispatching_next(): void
    {
        for ($i = 1001; $i <= 1050; $i++) {
            Statement::factory()->create(['id' => $i]);
        }
        $elastic = ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableChunkReverse Min Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 1050, 100);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, range(1050, 1001));

        Queue::assertNotPushed(StatementElasticSearchableChunkReverse::class);
    }

    public function test_job_preserves_range_mode_when_dispatching_next_lower_job(): void
    {
        for ($i = 9399; $i <= 9500; $i++) {
            Statement::factory()->create(['id' => $i]);
        }
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(9001, 9500, 100, false);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, range(9500, 9400));

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
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 2000, 20);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, array_reverse($existingIds));

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
