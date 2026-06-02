<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class StatementsPruneOld extends Command
{
    use CommandTrait;

    protected $signature = 'statements:prune-old
        {--days=180 : Number of calendar days to retain}
        {--batch=100000 : Rows to delete per batch}
        {--max-batches= : Maximum batches to run per table}
        {--sleep-ms=0 : Milliseconds to pause between batches}
        {--connection= : Database connection to use}
        {--skip-elastic : Skip Elasticsearch delete-by-query}
        {--elastic-sync : Wait for Elasticsearch delete-by-query to finish instead of starting a background task}';

    protected $description = 'Prune old statements and platform PUIDs in small database-side batches';

    /**
     * @var array<string, string>
     */
    private array $tables = [
        'statements_beta' => 'statements',
        'platform_puids' => 'platform PUIDs',
    ];

    public function handle(StatementElasticSearchService $statementElasticSearchService): int
    {
        try {
            $days = $this->positiveIntOption('days');
            $batchSize = $this->positiveIntOption('batch');
            $maxBatches = $this->nullablePositiveIntOption('max-batches');
            $sleepMs = $this->nonNegativeIntOption('sleep-ms');
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return Command::FAILURE;
        }

        $connectionName = $this->option('connection') ?: config('database.default');
        $connection = DB::connection($connectionName);
        $cutoff = Carbon::today()->subDays($days);

        if ($connection->getDriverName() === 'pgsql') {
            $this->setPostgresTimeouts($connection);
        }

        $this->info(sprintf(
            'Pruning rows created before %s in batches of %s.',
            $cutoff->toDateTimeString(),
            number_format($batchSize)
        ));

        Log::info('Statements prune old started', [
            'connection' => $connectionName,
            'cutoff' => $cutoff->toDateTimeString(),
            'batch_size' => $batchSize,
            'max_batches' => $maxBatches,
        ]);

        if (! $this->option('skip-elastic')) {
            try {
                $this->pruneElastic($statementElasticSearchService, $cutoff, (bool) $this->option('elastic-sync'));
            } catch (Throwable $exception) {
                $this->error('Elasticsearch prune failed: '.$exception->getMessage());

                Log::error('Statements prune old Elasticsearch delete failed', [
                    'cutoff' => $cutoff->toDateTimeString(),
                    'error' => $exception->getMessage(),
                ]);

                return Command::FAILURE;
            }
        }

        $totalDeleted = 0;

        foreach ($this->tables as $table => $label) {
            $deleted = $this->pruneTable($connection, $table, $label, $cutoff, $batchSize, $maxBatches, $sleepMs);
            $totalDeleted += $deleted;
        }

        $this->info(sprintf('Prune complete. Deleted %s rows total.', number_format($totalDeleted)));

        Log::info('Statements prune old completed', [
            'connection' => $connectionName,
            'cutoff' => $cutoff->toDateTimeString(),
            'deleted' => $totalDeleted,
        ]);

        return Command::SUCCESS;
    }

    private function pruneElastic(
        StatementElasticSearchService $statementElasticSearchService,
        Carbon $cutoff,
        bool $waitForCompletion
    ): void {
        if (! $statementElasticSearchService->isConfigured()) {
            throw new RuntimeException('Elasticsearch is not configured. Use --skip-elastic to prune only the database tables.');
        }

        $result = $statementElasticSearchService->deleteStatementsBeforeDate($cutoff, $waitForCompletion);
        $task = $result['task'] ?? null;

        if (is_string($task) && $task !== '') {
            $this->info('Elasticsearch prune task started: '.$task);
        } else {
            $deleted = $result['deleted'] ?? 0;
            $this->info(sprintf('Elasticsearch prune completed. Deleted %s statements.', number_format((int) $deleted)));
        }

        Log::info('Statements prune old Elasticsearch delete started', [
            'cutoff' => $cutoff->toDateTimeString(),
            'wait_for_completion' => $waitForCompletion,
            'task' => $task,
            'deleted' => $result['deleted'] ?? null,
            'response' => $result,
        ]);
    }

    private function pruneTable(
        Connection $connection,
        string $table,
        string $label,
        Carbon $cutoff,
        int $batchSize,
        ?int $maxBatches,
        int $sleepMs
    ): int {
        $this->line(sprintf('Pruning %s...', $label));

        $totalDeleted = 0;
        $batch = 0;

        do {
            $deleted = $this->deleteBatch($connection, $table, $cutoff, $batchSize);
            $batch++;
            $totalDeleted += $deleted;

            if ($deleted > 0) {
                $this->line(sprintf(
                    '%s batch %s deleted %s rows.',
                    $label,
                    number_format($batch),
                    number_format($deleted)
                ));
            }

            if ($deleted > 0 && $sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        } while ($deleted > 0 && ($maxBatches === null || $batch < $maxBatches));

        $this->info(sprintf('Deleted %s old %s rows.', number_format($totalDeleted), $label));

        Log::info('Statements prune old table completed', [
            'table' => $table,
            'deleted' => $totalDeleted,
            'batches' => $batch,
            'max_batches_reached' => $maxBatches !== null && $batch >= $maxBatches && $deleted > 0,
        ]);

        return $totalDeleted;
    }

    private function deleteBatch(Connection $connection, string $table, Carbon $cutoff, int $batchSize): int
    {
        if ($connection->getDriverName() === 'pgsql') {
            return $connection->delete(
                sprintf(
                    'WITH victims AS (
                        SELECT ctid
                        FROM %s
                        WHERE created_at < ?
                        ORDER BY created_at ASC, id ASC
                        LIMIT ?
                        FOR UPDATE SKIP LOCKED
                    )
                    DELETE FROM %s AS target
                    USING victims
                    WHERE target.ctid = victims.ctid',
                    $table,
                    $table
                ),
                [$cutoff, $batchSize]
            );
        }

        return $connection
            ->table($table)
            ->whereIn('id', function ($query) use ($table, $cutoff, $batchSize): void {
                $query
                    ->select('id')
                    ->from($table)
                    ->where('created_at', '<', $cutoff)
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->limit($batchSize);
            })
            ->delete();
    }

    private function setPostgresTimeouts(Connection $connection): void
    {
        $connection->statement("SET lock_timeout = '2s'");
        $connection->statement("SET statement_timeout = '5min'");
    }
}
