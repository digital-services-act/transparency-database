<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QueueNuke extends Command
{
    use CommandTrait;

    private const array QUEUE_TABLES = [
        'jobs' => 'id',
        'failed_jobs' => 'id',
        'job_batches' => 'id',
    ];

    private const int POSTGRES_LOCK_TIMEOUT_MILLISECONDS = 1000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:nuke
        {--batch=1000 : Number of rows to delete per batch}
        {--timeout=60 : Seconds to retry when rows are locked before failing}
        {--sleep=250 : Milliseconds to wait between locked-row retries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all queue table rows in batches and restart queue workers';

    public function handle(): int
    {
        $batchSize = $this->positiveIntOption('batch');
        $timeoutSeconds = $this->nonNegativeIntOption('timeout');
        $sleepMilliseconds = $this->nonNegativeIntOption('sleep');

        $this->call('queue:restart');

        foreach (self::QUEUE_TABLES as $table => $key) {
            try {
                $deleted = $this->deleteRows($table, $key, $batchSize, $timeoutSeconds, $sleepMilliseconds);
            } catch (RuntimeException $exception) {
                $this->error($exception->getMessage());

                return self::FAILURE;
            }

            $this->info(sprintf('Deleted %d rows from [%s].', $deleted, $table));
        }

        $this->call('queue:restart');

        return self::SUCCESS;
    }

    private function deleteRows(
        string $table,
        string $key,
        int $batchSize,
        int $timeoutSeconds,
        int $sleepMilliseconds
    ): int {
        if (DB::connection()->getDriverName() === 'pgsql') {
            return $this->deletePostgresRows($table, $key, $batchSize, $timeoutSeconds, $sleepMilliseconds);
        }

        return $this->deleteRowsByKey($table, $key, $batchSize);
    }

    private function deleteRowsByKey(string $table, string $key, int $batchSize): int
    {
        $deleted = 0;

        do {
            $ids = DB::table($table)
                ->orderBy($key)
                ->limit($batchSize)
                ->pluck($key);

            if ($ids->isEmpty()) {
                return $deleted;
            }

            $deleted += DB::table($table)
                ->whereIn($key, $ids->all())
                ->delete();
        } while (true);
    }

    private function deletePostgresRows(
        string $table,
        string $key,
        int $batchSize,
        int $timeoutSeconds,
        int $sleepMilliseconds
    ): int {
        $deleted = 0;
        $lockedSince = null;

        do {
            try {
                $deletedThisBatch = $this->deletePostgresBatch($table, $key, $batchSize);
            } catch (QueryException $exception) {
                if (! $this->isRetryablePostgresLockException($exception)) {
                    throw $exception;
                }

                $deletedThisBatch = 0;
            }

            if ($deletedThisBatch > 0) {
                $deleted += $deletedThisBatch;
                $lockedSince = null;

                continue;
            }

            $remaining = $this->countPostgresRows($table);

            if ($remaining === 0) {
                return $deleted;
            }

            $lockedSince ??= microtime(true);

            if ($timeoutSeconds === 0 || microtime(true) - $lockedSince >= $timeoutSeconds) {
                $remainingMessage = $remaining === null
                    ? 'the table could not be checked because it is still locked'
                    : sprintf('%d rows remain', $remaining);

                throw new RuntimeException(sprintf(
                    'Timed out clearing [%s]; %s.',
                    $table,
                    $remainingMessage
                ));
            }

            $this->sleepForMilliseconds($sleepMilliseconds);
        } while (true);
    }

    private function deletePostgresBatch(string $table, string $key, int $batchSize): int
    {
        $wrappedTable = DB::connection()->getQueryGrammar()->wrapTable($table);
        $wrappedKey = DB::connection()->getQueryGrammar()->wrap($key);

        return DB::transaction(function () use ($wrappedTable, $wrappedKey, $batchSize) {
            $this->setPostgresLockTimeout();

            return DB::delete(
                sprintf(
                    'delete from %s where %s in (
                        select %s from %s
                        order by %s
                        limit ?
                        for update skip locked
                    )',
                    $wrappedTable,
                    $wrappedKey,
                    $wrappedKey,
                    $wrappedTable,
                    $wrappedKey
                ),
                [$batchSize]
            );
        });
    }

    private function countPostgresRows(string $table): ?int
    {
        try {
            return DB::transaction(function () use ($table) {
                $this->setPostgresLockTimeout();

                return DB::table($table)->count();
            });
        } catch (QueryException $exception) {
            if ($this->isRetryablePostgresLockException($exception)) {
                return null;
            }

            throw $exception;
        }
    }

    private function setPostgresLockTimeout(): void
    {
        DB::statement(sprintf(
            "set local lock_timeout = '%dms'",
            self::POSTGRES_LOCK_TIMEOUT_MILLISECONDS
        ));
    }

    private function isRetryablePostgresLockException(QueryException $exception): bool
    {
        return in_array($exception->getCode(), ['40P01', '55P03'], true);
    }

    private function sleepForMilliseconds(int $milliseconds): void
    {
        if ($milliseconds <= 0) {
            return;
        }

        usleep($milliseconds * 1000);
    }
}
