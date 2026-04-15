<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class StatementBackfillTargetService
{
    public function getConfiguredStartId(): int
    {
        return (int) config('backfill.start_id');
    }

    public function getConfiguredEndId(): int
    {
        return (int) config('backfill.end_id');
    }

    public function getConfiguredChunkSize(): int
    {
        return (int) config('backfill.chunk_size');
    }

    public function getConfiguredTable(): string
    {
        return (string) config('backfill.table');
    }

    public function getConfiguredQueue(): string
    {
        return (string) config('backfill.queue');
    }

    public function getLastImportedId(): int
    {
        $response = $this->request()
            ->get($this->buildUrl((string) config('backfill.last_imported_path')))
            ->throw();

        $lastImportedId = $response->json('last_imported_id')
            ?? $response->json('highest_imported_id')
            ?? $response->json('lowest_id');

        if (! is_numeric($lastImportedId)) {
            throw new RuntimeException('Backfill target did not return a numeric imported id.');
        }

        return (int) $lastImportedId;
    }

    /**
     * @param  array<int, array<string, mixed>>  $statements
     */
    public function sendStatements(array $statements): void
    {
        $this->request()
            ->post($this->buildUrl((string) config('backfill.statements_path')), [
                'statements' => $statements,
            ])
            ->throw();
    }

    private function request()
    {
        $token = (string) config('backfill.token');
        if ($token === '') {
            throw new RuntimeException('BACKFILL_TOKEN is not configured.');
        }

        $timeout = (int) config('backfill.timeout');
        $retryTimes = (int) config('backfill.retry_times');
        $retrySleepMs = (int) config('backfill.retry_sleep_ms');

        return Http::acceptJson()
            ->asJson()
            ->withToken($token)
            ->timeout($timeout)
            ->connectTimeout(min($timeout, 10))
            ->retry($retryTimes, $retrySleepMs);
    }

    private function buildUrl(string $path): string
    {
        $baseUrl = rtrim((string) config('backfill.base_url'), '/');
        if ($baseUrl === '') {
            throw new RuntimeException('BACKFILL_BASE_URL is not configured.');
        }

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
