<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\PlatformPuidDeleteChunk;
use App\Jobs\StatementDeleteChunk;
use App\Services\DayArchiveService;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class StatementsRemoveDateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_dispatches_jobs_when_ids_are_found(): void
    {
        Queue::fake();
        Log::spy();

        // Mock DayArchiveService to return min/max IDs
        $this->mock(DayArchiveService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getFirstIdOfDate')->andReturn(1);
            $mock->shouldReceive('getLastIdOfDate')->andReturn(1000);
            $mock->shouldReceive('getFirstPlatformPuidIdOfDate')->andReturn(100);
            $mock->shouldReceive('getLastPlatformPuidIdOfDate')->andReturn(2000);
        });

        // Mock StatementElasticSearchService
        $this->mock(StatementElasticSearchService::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteStatementsForDate')->once();
        });

        $this->artisan('statements:remove-date 2025-09-04')
            ->assertExitCode(0);

        // Assert jobs were dispatched
        Queue::assertPushed(StatementDeleteChunk::class);
        Queue::assertPushed(PlatformPuidDeleteChunk::class);

        // Assert no warnings were logged
        Log::shouldNotHaveReceived('warning');
    }

    /**
     * @test
     */
    public function it_logs_a_warning_when_ids_are_not_found(): void
    {
        Queue::fake();
        Log::spy();

        // Mock DayArchiveService to return nulls
        $this->mock(DayArchiveService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getFirstIdOfDate')->andReturn(null);
            $mock->shouldReceive('getLastIdOfDate')->andReturn(null);
            $mock->shouldReceive('getFirstPlatformPuidIdOfDate')->andReturn(null);
            $mock->shouldReceive('getLastPlatformPuidIdOfDate')->andReturn(null);
        });

        // Mock StatementElasticSearchService
        $this->mock(StatementElasticSearchService::class, function (MockInterface $mock) {
            $mock->shouldReceive('deleteStatementsForDate')->once();
        });

        $this->artisan('statements:remove-date 2025-09-04')
            ->assertExitCode(0);

        // Assert jobs were NOT dispatched
        Queue::assertNotPushed(StatementDeleteChunk::class);
        Queue::assertNotPushed(PlatformPuidDeleteChunk::class);

        // Assert warnings were logged
        Log::shouldHaveReceived('warning')->twice();
    }
}
