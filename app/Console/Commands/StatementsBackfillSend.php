<?php

namespace App\Console\Commands;

use App\Jobs\StatementBackfillSendChunk;
use App\Services\StatementBackfillTargetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class StatementsBackfillSend extends Command
{
    protected $signature = 'statements:backfill-send
        {--start-id= : Override the configured sentinel start id}
        {--end-id= : Override the configured exclusive end id}
        {--chunk= : Override the configured chunk size}
        {--direction= : Override the configured send direction: asc or desc}';

    protected $description = 'Queue statement backfill jobs to send statements_beta rows to a remote environment';

    public function handle(StatementBackfillTargetService $backfillTargetService): int
    {
        $configuredStartId = $this->resolveIntOption('start-id', $backfillTargetService->getConfiguredStartId());
        $configuredEndId = $this->resolveIntOption('end-id', $backfillTargetService->getConfiguredEndId());
        $chunkSize = $this->resolveIntOption('chunk', $backfillTargetService->getConfiguredChunkSize());
        $direction = StatementBackfillSendChunk::normalizeDirection(
            $this->option('direction') === null
                ? $backfillTargetService->getConfiguredDirection()
                : (string) $this->option('direction')
        );

        if ($chunkSize < 1) {
            throw new RuntimeException('Chunk size must be greater than zero.');
        }

        if ($configuredEndId <= $configuredStartId) {
            throw new RuntimeException('The backfill end id must be greater than the start id.');
        }

        $importedBoundaryId = $backfillTargetService->getImportedBoundaryId($direction);

        if ($direction === StatementBackfillSendChunk::DIRECTION_DESC) {
            return $this->queueDescendingBackfill(
                $configuredStartId,
                $configuredEndId,
                $chunkSize,
                $importedBoundaryId,
                $direction
            );
        }

        return $this->queueAscendingBackfill(
            $configuredStartId,
            $configuredEndId,
            $chunkSize,
            $importedBoundaryId,
            $direction
        );
    }

    private function queueAscendingBackfill(
        int $configuredStartId,
        int $configuredEndId,
        int $chunkSize,
        int $importedBoundaryId,
        string $direction
    ): int {
        $firstIdToSend = max($importedBoundaryId, $configuredStartId) + 1;
        $maxIdToSend = $configuredEndId - 1;

        if ($firstIdToSend > $maxIdToSend) {
            return $this->nothingToQueue(
                'No backfill work to queue. The remote target is already at or beyond the configured end id.',
                $importedBoundaryId,
                $configuredStartId,
                $configuredEndId,
                $direction
            );
        }

        StatementBackfillSendChunk::dispatch($firstIdToSend, $maxIdToSend, $chunkSize, $direction);

        Log::info('StatementsBackfillSend queued initial job', [
            'remote_imported_boundary_id' => $importedBoundaryId,
            'first_id_to_send' => $firstIdToSend,
            'max_id_to_send' => $maxIdToSend,
            'chunk_size' => $chunkSize,
            'direction' => $direction,
        ]);

        $this->info(sprintf(
            'Queued backfill from id %d to %d in chunks of %d. Remote last imported id: %d.',
            $firstIdToSend,
            $maxIdToSend,
            $chunkSize,
            $importedBoundaryId
        ));

        return self::SUCCESS;
    }

    private function queueDescendingBackfill(
        int $configuredStartId,
        int $configuredEndId,
        int $chunkSize,
        int $importedBoundaryId,
        string $direction
    ): int {
        $minIdToSend = $configuredStartId + 1;
        $maxIdToSend = min($importedBoundaryId, $configuredEndId) - 1;

        if ($maxIdToSend < $minIdToSend) {
            return $this->nothingToQueue(
                'No backfill work to queue. The remote target is already at or below the configured start id.',
                $importedBoundaryId,
                $configuredStartId,
                $configuredEndId,
                $direction
            );
        }

        StatementBackfillSendChunk::dispatch($minIdToSend, $maxIdToSend, $chunkSize, $direction);

        Log::info('StatementsBackfillSend queued initial job', [
            'remote_imported_boundary_id' => $importedBoundaryId,
            'min_id_to_send' => $minIdToSend,
            'max_id_to_send' => $maxIdToSend,
            'chunk_size' => $chunkSize,
            'direction' => $direction,
        ]);

        $this->info(sprintf(
            'Queued descending backfill from id %d down to %d in chunks of %d. Remote imported boundary id: %d.',
            $maxIdToSend,
            $minIdToSend,
            $chunkSize,
            $importedBoundaryId
        ));

        return self::SUCCESS;
    }

    private function nothingToQueue(
        string $message,
        int $importedBoundaryId,
        int $configuredStartId,
        int $configuredEndId,
        string $direction
    ): int {
        $this->info($message);
        Log::info('StatementsBackfillSend no-op', [
            'remote_imported_boundary_id' => $importedBoundaryId,
            'start_id' => $configuredStartId,
            'end_id_exclusive' => $configuredEndId,
            'direction' => $direction,
        ]);

        return self::SUCCESS;
    }

    private function resolveIntOption(string $option, int $default): int
    {
        $value = $this->option($option);
        if ($value === null) {
            return $default;
        }

        if (! is_numeric($value)) {
            throw new RuntimeException(sprintf('The %s option must be numeric.', $option));
        }

        return (int) $value;
    }
}
