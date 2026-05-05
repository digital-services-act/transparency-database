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

    public function testFirstAttemptDispatchesNextChunk(): void
    {
        Queue::fake();

        $service = $this->mockBackfillTargetService();
        $this->mockStatementQuery([100, 101]);

        $job = new StatementBackfillSendChunk(100, 250, 100);
        $job->handle($service);

        Queue::assertPushed(StatementBackfillSendChunk::class, function (StatementBackfillSendChunk $job): bool {
            return $job->min === 200
                && $job->max === 250
                && $job->chunk === 100;
        });
    }

    public function testRetryDoesNotDispatchNextChunk(): void
    {
        Queue::fake();

        $service = $this->mockBackfillTargetService();
        $this->mockStatementQuery([100, 101]);

        $job = new StatementBackfillSendChunk(100, 250, 100);

        $queueJob = Mockery::mock(QueueJob::class);
        $queueJob->shouldReceive('attempts')->once()->andReturn(2);
        $job->job = $queueJob;

        $job->handle($service);

        Queue::assertNotPushed(StatementBackfillSendChunk::class);
    }

    private function mockBackfillTargetService(): StatementBackfillTargetService
    {
        $service = Mockery::mock(StatementBackfillTargetService::class);
        $service->shouldReceive('getConfiguredTable')
            ->once()
            ->andReturn('statements_beta');
        $service->shouldReceive('sendStatements')
            ->once()
            ->with([
                ['id' => 100],
                ['id' => 101],
            ]);

        return $service;
    }

    /**
     * @param  array<int, int>  $ids
     */
    private function mockStatementQuery(array $ids): void
    {
        $query = Mockery::mock();

        DB::shouldReceive('table')
            ->once()
            ->with('statements_beta')
            ->andReturn($query);

        $query->shouldReceive('whereBetween')
            ->once()
            ->with('id', [100, 199])
            ->andReturnSelf();

        $query->shouldReceive('orderBy')
            ->once()
            ->with('id')
            ->andReturnSelf();

        $query->shouldReceive('get')
            ->once()
            ->andReturn(collect(array_map(
                static fn(int $id): object => (object) ['id' => $id],
                $ids
            )));
    }
}
