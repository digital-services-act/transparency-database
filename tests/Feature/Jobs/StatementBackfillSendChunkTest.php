<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementBackfillSendChunk;
use App\Services\StatementBackfillTargetService;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\CreatesApplication;

class StatementBackfillSendChunkTest extends BaseTestCase
{
    use CreatesApplication;

    public function test_first_attempt_dispatches_next_chunk(): void
    {
        Queue::fake();

        $service = $this->mockBackfillTargetService([100, 101]);
        $this->mockStatementQuery([100, 199], [100, 101]);

        $job = new StatementBackfillSendChunk(100, 250, 100);
        $job->handle($service);

        Queue::assertPushed(StatementBackfillSendChunk::class, function (StatementBackfillSendChunk $job): bool {
            return $job->min === 200
                && $job->max === 250
                && $job->chunk === 100
                && $job->direction === StatementBackfillSendChunk::DIRECTION_ASC;
        });
    }

    public function test_retry_does_not_dispatch_next_chunk(): void
    {
        Queue::fake();

        $service = $this->mockBackfillTargetService([100, 101]);
        $this->mockStatementQuery([100, 199], [100, 101]);

        $job = new StatementBackfillSendChunk(100, 250, 100);

        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle($service);

        Queue::assertNotPushed(StatementBackfillSendChunk::class);
    }

    public function test_descending_first_attempt_dispatches_previous_chunk(): void
    {
        Queue::fake();

        $service = $this->mockBackfillTargetService([250, 249]);
        $this->mockStatementQuery([151, 250], [250, 249], StatementBackfillSendChunk::DIRECTION_DESC);

        $job = new StatementBackfillSendChunk(100, 250, 100, StatementBackfillSendChunk::DIRECTION_DESC);
        $job->handle($service);

        Queue::assertPushed(StatementBackfillSendChunk::class, function (StatementBackfillSendChunk $job): bool {
            return $job->min === 100
                && $job->max === 150
                && $job->chunk === 100
                && $job->direction === StatementBackfillSendChunk::DIRECTION_DESC;
        });
    }

    public function test_descending_retry_does_not_dispatch_previous_chunk(): void
    {
        Queue::fake();

        $service = $this->mockBackfillTargetService([250, 249]);
        $this->mockStatementQuery([151, 250], [250, 249], StatementBackfillSendChunk::DIRECTION_DESC);

        $job = new StatementBackfillSendChunk(100, 250, 100, StatementBackfillSendChunk::DIRECTION_DESC);

        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle($service);

        Queue::assertNotPushed(StatementBackfillSendChunk::class);
    }

    /**
     * @param  array<int, int>  $expectedIds
     */
    private function mockBackfillTargetService(array $expectedIds): StatementBackfillTargetService
    {
        $service = Mockery::mock(StatementBackfillTargetService::class);
        $service->shouldReceive('getConfiguredTable')
            ->once()
            ->andReturn('statements_beta');
        $service->shouldReceive('sendStatements')
            ->once()
            ->with(array_map(
                static fn (int $id): array => ['id' => $id],
                $expectedIds
            ));

        return $service;
    }

    /**
     * @param  array<int, int>  $ids
     */
    private function mockStatementQuery(
        array $expectedRange,
        array $ids,
        string $direction = StatementBackfillSendChunk::DIRECTION_ASC
    ): void {
        $query = Mockery::mock();

        DB::shouldReceive('table')
            ->once()
            ->with('statements_beta')
            ->andReturn($query);

        $query->shouldReceive('whereBetween')
            ->once()
            ->with('id', $expectedRange)
            ->andReturnSelf();

        if ($direction === StatementBackfillSendChunk::DIRECTION_DESC) {
            $query->shouldReceive('orderByDesc')
                ->once()
                ->with('id')
                ->andReturnSelf();
        } else {
            $query->shouldReceive('orderBy')
                ->once()
                ->with('id')
                ->andReturnSelf();
        }

        $query->shouldReceive('get')
            ->once()
            ->andReturn(collect(array_map(
                static fn (int $id): object => (object) ['id' => $id],
                $ids
            )));
    }
}
