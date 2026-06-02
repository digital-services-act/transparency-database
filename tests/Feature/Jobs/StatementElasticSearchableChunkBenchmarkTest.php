<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementElasticSearchableChunk;
use App\Jobs\StatementElasticSearchableChunkReverse;
use App\Jobs\StatementElasticSearchableRawChunk;
use App\Jobs\StatementElasticSearchableRawChunkReverse;
use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class StatementElasticSearchableChunkBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    private $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = Mockery::mock(StatementElasticSearchService::class);
        $this->app->instance(StatementElasticSearchService::class, $this->mockService);
    }

    public function test_forward_eloquent_job_logs_benchmark_timings_when_enabled(): void
    {
        foreach ([1001, 1002, 1003] as $id) {
            Statement::factory()->create(['id' => $id]);
        }

        Cache::shouldReceive('get')->with('stop_reindexing', false)->andReturn(false);

        $this->mockService->shouldReceive('benchmarkBulkIndexStatements')
            ->once()
            ->andReturn($this->serviceMetrics());

        Log::shouldReceive('info')
            ->once()
            ->with('StatementElasticSearchableChunk benchmark', Mockery::on(fn (array $context): bool => $this->assertBenchmarkContext($context, [
                'min' => 1001,
                'end' => 1003,
                'range' => true,
            ])));

        Queue::fake();

        $job = new StatementElasticSearchableChunk(1001, 2000, 2, true, true);
        $job->handle($this->mockService);

        Queue::assertPushed(StatementElasticSearchableChunk::class, fn ($job): bool => $job->benchmark === true);
    }

    public function test_reverse_eloquent_job_logs_benchmark_timings_when_enabled(): void
    {
        foreach ([1998, 1999, 2000] as $id) {
            Statement::factory()->create(['id' => $id]);
        }

        Cache::shouldReceive('get')->with('stop_reindexing', false)->andReturn(false);

        $this->mockService->shouldReceive('benchmarkBulkIndexStatements')
            ->once()
            ->andReturn($this->serviceMetrics());

        Log::shouldReceive('info')
            ->once()
            ->with('StatementElasticSearchableChunkReverse benchmark', Mockery::on(fn (array $context): bool => $this->assertBenchmarkContext($context, [
                'min' => 1001,
                'end' => 1998,
                'range' => true,
            ])));

        Queue::fake();

        $job = new StatementElasticSearchableChunkReverse(1001, 2000, 2, true, true);
        $job->handle($this->mockService);

        Queue::assertPushed(StatementElasticSearchableChunkReverse::class, fn ($job): bool => $job->benchmark === true);
    }

    public function test_forward_raw_job_logs_benchmark_timings_when_enabled(): void
    {
        Cache::shouldReceive('get')->with('stop_reindexing', false)->andReturn(false);

        $this->mockService->shouldReceive('benchmarkBulkIndexRawStatementsForIdRange')
            ->once()
            ->with(1001, 1003, true)
            ->andReturn($this->rawServiceMetrics());

        Log::shouldReceive('info')
            ->once()
            ->with('StatementElasticSearchableRawChunk benchmark', Mockery::on(fn (array $context): bool => $this->assertBenchmarkContext($context, [
                'min' => 1001,
                'end' => 1003,
                'range' => true,
            ])));

        Queue::fake();

        $job = new StatementElasticSearchableRawChunk(1001, 2000, 2, true, true);
        $job->handle($this->mockService);

        Queue::assertPushed(StatementElasticSearchableRawChunk::class, fn ($job): bool => $job->benchmark === true);
    }

    public function test_reverse_raw_job_logs_benchmark_timings_when_enabled(): void
    {
        Cache::shouldReceive('get')->with('stop_reindexing', false)->andReturn(false);

        $this->mockService->shouldReceive('benchmarkBulkIndexRawStatementsForIdRange')
            ->once()
            ->with(1998, 2000, true, 'desc')
            ->andReturn($this->rawServiceMetrics());

        Log::shouldReceive('info')
            ->once()
            ->with('StatementElasticSearchableRawChunkReverse benchmark', Mockery::on(fn (array $context): bool => $this->assertBenchmarkContext($context, [
                'min' => 1001,
                'end' => 1998,
                'range' => true,
            ])));

        Queue::fake();

        $job = new StatementElasticSearchableRawChunkReverse(1001, 2000, 2, true, true);
        $job->handle($this->mockService);

        Queue::assertPushed(StatementElasticSearchableRawChunkReverse::class, fn ($job): bool => $job->benchmark === true);
    }

    private function serviceMetrics(): array
    {
        return [
            'rows' => 3,
            'transform_ms' => 1.2,
            'ndjson_ms' => 0.3,
            'elastic_ms' => 4.5,
            'payload_bytes' => 123,
            'payload_mb' => 0.0001,
            'total_ms' => 6.0,
        ];
    }

    private function rawServiceMetrics(): array
    {
        return ['fetch_ms' => 0.4] + $this->serviceMetrics();
    }

    private function assertBenchmarkContext(array $context, array $expected): bool
    {
        foreach ($expected as $key => $value) {
            if (($context[$key] ?? null) !== $value) {
                return false;
            }
        }

        foreach (['rows', 'transform_ms', 'ndjson_ms', 'elastic_ms', 'payload_bytes', 'payload_mb', 'fetch_ms', 'total_ms'] as $key) {
            if (! array_key_exists($key, $context)) {
                return false;
            }
        }

        return $context['elastic_ms'] === 4.5
            && $context['total_ms'] >= 6.0;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
