<?php

namespace App\Console\Commands;

use App\Jobs\StatementBackfillSendChunk;
use App\Services\StatementBackfillTargetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StatementsBackfillSend extends Command
{
    protected $signature = 'statements:backfill-send
        {--start-id= : Override the configured sentinel start id}
        {--end-id= : Override the configured exclusive end id}
        {--chunk= : Override the configured chunk size}';

    protected $description = 'Queue statement backfill jobs to send statements_beta rows to a remote environment';

    public function handle(StatementBackfillTargetService $backfillTargetService): int
    {
        $configuredStartId = $this->resolveIntOption('start-id', $backfillTargetService->getConfiguredStartId());
        $configuredEndId = $this->resolveIntOption('end-id', $backfillTargetService->getConfiguredEndId());
        $chunkSize = $this->resolveIntOption('chunk', $backfillTargetService->getConfiguredChunkSize());

        if ($chunkSize < 1) {
            throw new RuntimeException('Chunk size must be greater than zero.');
        }

        if ($configuredEndId <= $configuredStartId) {
            throw new RuntimeException('The backfill end id must be greater than the start id.');
        }

        $lastImportedId = $backfillTargetService->getLastImportedId();
        $firstIdToSend = max($lastImportedId, $configuredStartId) + 1;
        $maxIdToSend = $configuredEndId - 1;

        if ($firstIdToSend > $maxIdToSend) {
            $message = 'No backfill work to queue. The remote target is already at or beyond the configured end id.';
            $this->info($message);
            Log::info('StatementsBackfillSend no-op', [
                'remote_last_imported_id' => $lastImportedId,
                'start_id' => $configuredStartId,
                'end_id_exclusive' => $configuredEndId,
            ]);

            return self::SUCCESS;
        }

        StatementBackfillSendChunk::dispatch($firstIdToSend, $maxIdToSend, $chunkSize);

        Log::info('StatementsBackfillSend queued initial job', [
            'remote_last_imported_id' => $lastImportedId,
            'first_id_to_send' => $firstIdToSend,
            'max_id_to_send' => $maxIdToSend,
            'chunk_size' => $chunkSize,
        ]);

        $this->info(sprintf(
            'Queued backfill from id %d to %d in chunks of %d. Remote last imported id: %d.',
            $firstIdToSend,
            $maxIdToSend,
            $chunkSize,
            $lastImportedId
        ));

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
