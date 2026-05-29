<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\QueueNuke;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery;
use PDOException;
use Tests\TestCase;
use Throwable;

class QueueNukeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_queue_tables_in_batches_and_restarts_queue(): void
    {
        foreach (range(1, 3) as $id) {
            DB::table('jobs')->insert([
                'id' => $id,
                'queue' => 'default',
                'payload' => 'test',
                'attempts' => 0,
                'reserved_at' => null,
                'available_at' => now()->unix(),
                'created_at' => now()->unix(),
            ]);

            DB::table('failed_jobs')->insert([
                'id' => $id,
                'uuid' => 'test-uuid-'.$id,
                'connection' => 'database',
                'queue' => 'default',
                'payload' => 'test',
                'exception' => 'test',
                'failed_at' => now(),
            ]);

            DB::table('job_batches')->insert([
                'id' => 'test-batch-id-'.$id,
                'name' => 'test batch',
                'total_jobs' => 1,
                'pending_jobs' => 1,
                'failed_jobs' => 0,
                'failed_job_ids' => '[]',
                'options' => null,
                'created_at' => now()->unix(),
                'cancelled_at' => null,
                'finished_at' => null,
            ]);
        }

        $this->assertDatabaseCount('jobs', 3);
        $this->assertDatabaseCount('failed_jobs', 3);
        $this->assertDatabaseCount('job_batches', 3);

        $this->artisan('queue:nuke', ['--batch' => 1])
            ->assertSuccessful();

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
        $this->assertDatabaseCount('job_batches', 0);
        $this->assertTrue(Cache::has('illuminate:queue:restart'));
    }

    public function test_it_deletes_postgres_queue_rows_with_skip_locked_batches(): void
    {
        $this->mockPostgresQueueTables(
            deleteResults: [2, 0],
            countResults: [0],
        );

        $deleted = $this->invokeQueueNukeMethod('deleteRows', ['jobs', 'id', 2, 60, 0]);

        $this->assertSame(2, $deleted);
    }

    public function test_it_retries_postgres_queue_rows_when_a_batch_is_locked(): void
    {
        $this->mockPostgresQueueTables(
            deleteResults: [0, 1, 0],
            countResults: [1, 0],
        );

        $deleted = $this->invokeQueueNukeMethod('deletePostgresRows', ['jobs', 'id', 2, 60, 0]);

        $this->assertSame(1, $deleted);
    }

    public function test_it_times_out_when_postgres_queue_rows_remain_locked(): void
    {
        $this->mockPostgresQueueTables(
            deleteResults: [0],
            countResults: [1],
        );

        $this->artisan('queue:nuke', [
            '--timeout' => 0,
            '--sleep' => 0,
        ])
            ->expectsOutputToContain('Timed out clearing [jobs]; 1 rows remain.')
            ->assertExitCode(1);
    }

    public function test_it_reports_timeout_when_locked_postgres_rows_cannot_be_counted(): void
    {
        $this->mockPostgresQueueTables(
            deleteResults: [$this->queryException('55P03')],
            countResults: [$this->queryException('55P03')],
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Timed out clearing [jobs]; the table could not be checked because it is still locked.');

        $this->invokeQueueNukeMethod('deletePostgresRows', ['jobs', 'id', 2, 0, 0]);
    }

    public function test_it_rethrows_non_retryable_postgres_delete_errors(): void
    {
        $this->mockPostgresQueueTables(
            deleteResults: [$this->queryException('99999')],
        );

        $this->expectException(QueryException::class);

        $this->invokeQueueNukeMethod('deletePostgresRows', ['jobs', 'id', 2, 60, 0]);
    }

    public function test_it_rethrows_non_retryable_postgres_count_errors(): void
    {
        $this->mockPostgresQueueTables(
            deleteResults: [0],
            countResults: [$this->queryException('99999')],
        );

        $this->expectException(QueryException::class);

        $this->invokeQueueNukeMethod('deletePostgresRows', ['jobs', 'id', 2, 60, 0]);
    }

    public function test_it_can_sleep_between_postgres_lock_retries(): void
    {
        $startedAt = microtime(true);

        $this->invokeQueueNukeMethod('sleepForMilliseconds', [1]);

        $this->assertGreaterThanOrEqual($startedAt, microtime(true));
    }

    private function mockPostgresQueueTables(array $deleteResults = [], array $countResults = []): void
    {
        $grammar = Mockery::mock();
        $grammar->shouldReceive('wrapTable')->with('jobs')->andReturn('"jobs"')->byDefault();
        $grammar->shouldReceive('wrap')->with('id')->andReturn('"id"')->byDefault();

        $connection = Mockery::mock();
        $connection->shouldReceive('getDriverName')->andReturn('pgsql')->byDefault();
        $connection->shouldReceive('getQueryGrammar')->andReturn($grammar)->byDefault();

        DB::shouldReceive('connection')->andReturn($connection)->byDefault();
        DB::shouldReceive('transaction')->andReturnUsing(static fn (callable $callback) => $callback())->byDefault();
        DB::shouldReceive('statement')->with("set local lock_timeout = '1000ms'")->andReturnTrue()->byDefault();
        DB::shouldReceive('delete')->andReturnUsing(function () use (&$deleteResults): int {
            $result = array_shift($deleteResults) ?? 0;

            if ($result instanceof Throwable) {
                throw $result;
            }

            return $result;
        })->byDefault();

        $table = Mockery::mock();
        $table->shouldReceive('count')->andReturnUsing(function () use (&$countResults): int {
            $result = array_shift($countResults) ?? 0;

            if ($result instanceof Throwable) {
                throw $result;
            }

            return $result;
        })->byDefault();

        DB::shouldReceive('table')->with('jobs')->andReturn($table)->byDefault();
    }

    private function invokeQueueNukeMethod(string $method, array $arguments): mixed
    {
        $reflection = new \ReflectionMethod(QueueNuke::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(new QueueNuke, $arguments);
    }

    private function queryException(string $code): QueryException
    {
        $exception = new QueryException('pgsql', 'select 1', [], new PDOException('Postgres lock issue'));

        (new \ReflectionProperty(\Exception::class, 'code'))->setValue($exception, $code);

        return $exception;
    }
}
