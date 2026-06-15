<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementElasticSearchableChunk;
use App\Jobs\StatementElasticSearchableChunkReverse;
use App\Jobs\StatementElasticSearchableRawChunk;
use App\Jobs\StatementElasticSearchableRawChunkReverse;
use App\Models\Statement;
use App\Services\StatementElasticIndexerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class StatementElasticSearchableChunkBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_forward_eloquent_job_logs_benchmark_timings_when_enabled(): void
    {
        foreach ([1001, 1002, 1003] as $id) {
            Statement::factory()->create(['id' => $id]);
        }

        ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with('StatementElasticSearchableChunk benchmark', Mockery::on(fn (array $context): bool => $this->assertBenchmarkContext($context, [
                'min' => 1001,
                'end' => 1003,
                'range' => true,
            ])));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 2, true, true);
        $job->handle($this->indexer());

        Queue::assertPushed(StatementElasticSearchableChunk::class, fn ($job): bool => $job->benchmark === true);
    }

    public function test_reverse_eloquent_job_logs_benchmark_timings_when_enabled(): void
    {
        foreach ([1998, 1999, 2000] as $id) {
            Statement::factory()->create(['id' => $id]);
        }

        ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with('StatementElasticSearchableChunkReverse benchmark', Mockery::on(fn (array $context): bool => $this->assertBenchmarkContext($context, [
                'min' => 1001,
                'end' => 1998,
                'range' => true,
            ])));

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 2000, 2, true, true);
        $job->handle($this->indexer());

        Queue::assertPushed(StatementElasticSearchableChunkReverse::class, fn ($job): bool => $job->benchmark === true);
    }

    public function test_forward_raw_job_logs_benchmark_timings_when_enabled(): void
    {
        foreach ([1001, 1002, 1003] as $id) {
            Statement::factory()->create(['id' => $id]);
        }

        ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with('StatementElasticSearchableRawChunk benchmark', Mockery::on(fn (array $context): bool => $this->assertBenchmarkContext($context, [
                'min' => 1001,
                'end' => 1003,
                'range' => true,
            ])));

        Queue::fake();

        $job = new StatementElasticSearchableRawChunk(1001, 2000, 2, true, true);
        $job->handle($this->indexer());

        Queue::assertPushed(StatementElasticSearchableRawChunk::class, fn ($job): bool => $job->benchmark === true);
    }

    public function test_reverse_raw_job_logs_benchmark_timings_when_enabled(): void
    {
        foreach ([1998, 1999, 2000] as $id) {
            Statement::factory()->create(['id' => $id]);
        }

        ElasticMocker::fake()->bulkReturns();

        Log::shouldReceive('info')
            ->once()
            ->with('StatementElasticSearchableRawChunkReverse benchmark', Mockery::on(fn (array $context): bool => $this->assertBenchmarkContext($context, [
                'min' => 1001,
                'end' => 1998,
                'range' => true,
            ])));

        Queue::fake();

        $job = new StatementElasticSearchableRawChunkReverse(1001, 2000, 2, true, true);
        $job->handle($this->indexer());

        Queue::assertPushed(StatementElasticSearchableRawChunkReverse::class, fn ($job): bool => $job->benchmark === true);
    }

    private function indexer(): StatementElasticIndexerService
    {
        return app(StatementElasticIndexerService::class);
    }

    private function assertBenchmarkContext(array $context, array $expected): bool
    {
        foreach ($expected as $key => $value) {
            if (($context[$key] ?? null) !== $value) {
                return false;
            }
        }

        foreach (['rows', 'transform_ms', 'ndjson_ms', 'elastic_ms', 'payload_bytes', 'payload_mb', 'fetch_ms', 'total_ms', 'elastic_attempts', 'elastic_retries', 'elastic_retry_sleep_ms'] as $key) {
            if (! array_key_exists($key, $context)) {
                return false;
            }
        }

        return $context['rows'] === 3
            && $context['elastic_attempts'] === 1
            && $context['elastic_retries'] === 0
            && $context['elastic_retry_sleep_ms'] === 0
            && $context['payload_bytes'] > 0
            && $context['total_ms'] >= 0;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
