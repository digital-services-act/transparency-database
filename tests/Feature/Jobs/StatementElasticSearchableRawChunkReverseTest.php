<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementElasticSearchableRawChunkReverse;
use App\Models\Statement;
use App\Services\StatementElasticIndexerService;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class StatementElasticSearchableRawChunkReverseTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_indexes_highest_raw_chunk_and_dispatches_next_lower_job(): void
    {
        Statement::factory()->create(['id' => 1900]);
        Statement::factory()->create(['id' => 2000]);
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableRawChunkReverse(1001, 2000, 100);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, [2000, 1900]);

        Queue::assertPushed(StatementElasticSearchableRawChunkReverse::class, function ($job) {
            return $job->min === 1001
                && $job->max === 1899
                && $job->chunk === 100
                && $job->range === true;
        });
    }

    public function test_retry_indexes_current_chunk_but_does_not_dispatch_next_lower_job(): void
    {
        Statement::factory()->create(['id' => 1900]);
        Statement::factory()->create(['id' => 2000]);
        $elastic = ElasticMocker::fake()->bulkReturns();

        Queue::fake();

        $job = new StatementElasticSearchableRawChunkReverse(1001, 2000, 100);
        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, [2000, 1900]);

        Queue::assertNotPushed(StatementElasticSearchableRawChunkReverse::class);
    }

    public function test_job_processes_final_lower_chunk_without_dispatching_next(): void
    {
        Statement::factory()->create(['id' => 1001]);
        Statement::factory()->create(['id' => 1050]);
        $elastic = ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with(Mockery::pattern('/StatementElasticSearchableRawChunkReverse Min Reached at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/'));

        Queue::fake();

        $job = new StatementElasticSearchableRawChunkReverse(1001, 1050, 100);
        $job->handle($this->indexer());

        $this->assertBulkIds($elastic, [1050, 1001]);

        Queue::assertNotPushed(StatementElasticSearchableRawChunkReverse::class);
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
