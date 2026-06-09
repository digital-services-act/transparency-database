<?php

namespace Tests\Feature\Jobs;

use App\Jobs\PlatformPuidDeleteChunk;
use App\Models\Platform;
use App\Models\PlatformPuid;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class PlatformPuidDeleteChunkTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_chunk_and_dispatches_next_job_on_first_attempt(): void
    {
        Queue::fake();
        $this->expectDelete(10, 15);

        $job = new PlatformPuidDeleteChunk(10, 20, 5, '2025-11-27');
        $job->handle();

        Queue::assertPushed(PlatformPuidDeleteChunk::class, function (PlatformPuidDeleteChunk $job): bool {
            return $job->min === 16
                && $job->max === 20
                && $job->chunk === 5
                && $job->date === '2025-11-27';
        });
    }

    public function test_retry_deletes_current_chunk_but_does_not_dispatch_next_job(): void
    {
        Queue::fake();
        $this->expectDelete(10, 15);

        $job = new PlatformPuidDeleteChunk(10, 20, 5, '2025-11-27');
        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle();

        Queue::assertNotPushed(PlatformPuidDeleteChunk::class);
    }

    public function test_it_deletes_final_chunk_and_logs_completion(): void
    {
        Queue::fake();
        Log::shouldReceive('info')->once()->with(Mockery::pattern('/PlatformPuidDeleteChunk Max Reached at/'));
        $this->expectDelete(18, 20);

        $job = new PlatformPuidDeleteChunk(18, 20, 5, '2025-11-27');
        $job->handle();

        Queue::assertNotPushed(PlatformPuidDeleteChunk::class);
    }

    public function test_it_respects_max_boundary_and_does_not_delete_beyond(): void
    {
        Queue::fake();
        Log::shouldReceive('info')->once()->with(Mockery::pattern('/PlatformPuidDeleteChunk Max Reached at/'));
        $this->expectDelete(95, 100);

        $job = new PlatformPuidDeleteChunk(95, 100, 10, '2025-11-27');
        $job->handle();

        Queue::assertNotPushed(PlatformPuidDeleteChunk::class);
    }

    public function test_it_ensures_complete_coverage_of_range(): void
    {
        Queue::fake();
        $this->expectDelete(1, 4);

        $job = new PlatformPuidDeleteChunk(1, 10, 3, '2025-11-27');
        $job->handle();

        Queue::assertPushed(PlatformPuidDeleteChunk::class, function (PlatformPuidDeleteChunk $job): bool {
            return $job->min === 5
                && $job->max === 10
                && $job->chunk === 3
                && $job->date === '2025-11-27';
        });
    }

    public function test_it_can_limit_deletes_to_a_specific_date(): void
    {
        $platformId = Platform::nonDsa()->first()->id;

        PlatformPuid::factory()->create([
            'id' => 10,
            'platform_id' => $platformId,
            'puid' => 'target-date-puid',
            'created_at' => '2025-11-27 12:00:00',
        ]);
        PlatformPuid::factory()->create([
            'id' => 11,
            'platform_id' => $platformId,
            'puid' => 'other-date-puid',
            'created_at' => '2025-11-28 12:00:00',
        ]);

        $job = new PlatformPuidDeleteChunk(10, 11, 10, '2025-11-27');
        $job->handle();

        $this->assertDatabaseMissing('platform_puids', ['puid' => 'target-date-puid']);
        $this->assertDatabaseHas('platform_puids', ['puid' => 'other-date-puid']);
    }

    private function expectDelete(int $start, int $end, string $date = '2025-11-27'): void
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = $startOfDay->copy()->addDay();

        DB::shouldReceive('table')->once()->with('platform_puids')->andReturnSelf();
        DB::shouldReceive('where')->once()->with('id', '>=', $start)->andReturnSelf();
        DB::shouldReceive('where')->once()->with('id', '<=', $end)->andReturnSelf();
        DB::shouldReceive('where')->once()->withArgs(fn (string $column, string $operator, Carbon $value): bool => $column === 'created_at'
            && $operator === '>='
            && $value->equalTo($startOfDay))->andReturnSelf();
        DB::shouldReceive('where')->once()->withArgs(fn (string $column, string $operator, Carbon $value): bool => $column === 'created_at'
            && $operator === '<'
            && $value->equalTo($endOfDay))->andReturnSelf();
        DB::shouldReceive('delete')->once()->andReturn(0);
    }
}
