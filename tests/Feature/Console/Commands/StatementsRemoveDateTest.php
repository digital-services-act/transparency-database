<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\PlatformPuidDeleteChunk;
use App\Jobs\StatementDeleteChunk;
use App\Services\DayArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class StatementsRemoveDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_jobs_when_ids_are_found(): void
    {
        Queue::fake();
        Log::spy();

        $this->mock(DayArchiveService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getFirstIdOfDate')->andReturn(1);
            $mock->shouldReceive('getLastIdOfDate')->andReturn(1000);
            $mock->shouldReceive('getFirstPlatformPuidIdOfDate')->andReturn(100);
            $mock->shouldReceive('getLastPlatformPuidIdOfDate')->andReturn(2000);
        });

        $elastic = ElasticMocker::fake()->deleteByQueryReturns();

        $this->artisan('statements:remove-date 2025-09-04')
            ->assertExitCode(0);

        $this->assertSame('POST', $elastic->requests()[0]->getMethod());
        $this->assertSame('/statement_index/_delete_by_query', $elastic->requests()[0]->getUri()->getPath());

        Queue::assertPushed(StatementDeleteChunk::class, 4);
        Queue::assertPushed(StatementDeleteChunk::class, fn (StatementDeleteChunk $job): bool => $job->min === 1
            && $job->max === 250
            && $job->chunk === 10000
            && $job->date === '2025-09-04');
        Queue::assertPushed(StatementDeleteChunk::class, fn (StatementDeleteChunk $job): bool => $job->min === 751
            && $job->max === 1000
            && $job->chunk === 10000
            && $job->date === '2025-09-04');

        Queue::assertPushed(PlatformPuidDeleteChunk::class, 4);
        Queue::assertPushed(PlatformPuidDeleteChunk::class, fn (PlatformPuidDeleteChunk $job): bool => $job->min === 100
            && $job->max === 575
            && $job->chunk === 10000
            && $job->date === '2025-09-04');
        Queue::assertPushed(PlatformPuidDeleteChunk::class, fn (PlatformPuidDeleteChunk $job): bool => $job->min === 1528
            && $job->max === 2000
            && $job->chunk === 10000
            && $job->date === '2025-09-04');

        Log::shouldNotHaveReceived('warning');
    }

    public function test_it_caps_delete_chains_at_four(): void
    {
        Queue::fake();
        Log::spy();

        $this->mock(DayArchiveService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getFirstIdOfDate')->andReturn(1);
            $mock->shouldReceive('getLastIdOfDate')->andReturn(800);
            $mock->shouldReceive('getFirstPlatformPuidIdOfDate')->andReturn(null);
            $mock->shouldReceive('getLastPlatformPuidIdOfDate')->andReturn(null);
        });

        ElasticMocker::fake()->deleteByQueryReturns();

        $this->artisan('statements:remove-date', [
            'date' => '2025-09-04',
            'chunk' => 1000,
            'chains' => 8,
        ])->assertExitCode(0);

        Queue::assertPushed(StatementDeleteChunk::class, 4);
        Queue::assertPushed(StatementDeleteChunk::class, fn (StatementDeleteChunk $job): bool => $job->min === 1 && $job->max === 200);
        Queue::assertPushed(StatementDeleteChunk::class, fn (StatementDeleteChunk $job): bool => $job->min === 601 && $job->max === 800);
    }

    public function test_it_stops_splitting_when_an_uneven_range_is_exhausted(): void
    {
        Queue::fake();
        Log::spy();

        $this->mock(DayArchiveService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getFirstIdOfDate')->andReturn(1);
            $mock->shouldReceive('getLastIdOfDate')->andReturn(5);
            $mock->shouldReceive('getFirstPlatformPuidIdOfDate')->andReturn(null);
            $mock->shouldReceive('getLastPlatformPuidIdOfDate')->andReturn(null);
        });

        ElasticMocker::fake()->deleteByQueryReturns();

        $this->artisan('statements:remove-date', [
            'date' => '2025-09-04',
            'chunk' => 100,
            'chains' => 4,
        ])->assertExitCode(0);

        Queue::assertPushed(StatementDeleteChunk::class, 3);
        Queue::assertPushed(StatementDeleteChunk::class, fn (StatementDeleteChunk $job): bool => $job->min === 1 && $job->max === 2);
        Queue::assertPushed(StatementDeleteChunk::class, fn (StatementDeleteChunk $job): bool => $job->min === 3 && $job->max === 4);
        Queue::assertPushed(StatementDeleteChunk::class, fn (StatementDeleteChunk $job): bool => $job->min === 5 && $job->max === 5);
    }

    public function test_it_logs_a_warning_when_ids_are_not_found(): void
    {
        Queue::fake();
        Log::spy();

        $this->mock(DayArchiveService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getFirstIdOfDate')->andReturn(null);
            $mock->shouldReceive('getLastIdOfDate')->andReturn(null);
            $mock->shouldReceive('getFirstPlatformPuidIdOfDate')->andReturn(null);
            $mock->shouldReceive('getLastPlatformPuidIdOfDate')->andReturn(null);
        });

        $elastic = ElasticMocker::fake()->deleteByQueryReturns();

        $this->artisan('statements:remove-date 2025-09-04')
            ->assertExitCode(0);

        $this->assertSame('POST', $elastic->requests()[0]->getMethod());
        $this->assertSame('/statement_index/_delete_by_query', $elastic->requests()[0]->getUri()->getPath());

        Queue::assertNotPushed(StatementDeleteChunk::class);
        Queue::assertNotPushed(PlatformPuidDeleteChunk::class);

        Log::shouldHaveReceived('warning')->twice();
    }

    public function test_it_rejects_invalid_chunk_arguments(): void
    {
        $this->artisan('statements:remove-date', [
            'date' => '2025-09-04',
            'chunk' => 0,
        ])
            ->expectsOutput('The chunk argument must be greater than zero.')
            ->assertExitCode(1);
    }
}
