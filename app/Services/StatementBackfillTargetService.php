<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class StatementBackfillTargetService
{
    private const JSON_STRING_COLUMNS = [
        'decision_visibility',
        'category_addition',
        'category_specification',
        'content_type',
        'territorial_scope',
    ];

    private const INTEGER_COLUMNS = [
        'id',
        'user_id',
        'platform_id',
    ];

    private const TIMESTAMP_COLUMNS = [
        'content_date',
        'application_date',
        'end_date_visibility_restriction',
        'end_date_monetary_restriction',
        'end_date_service_restriction',
        'end_date_account_restriction',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

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
        $normalizedStatements = array_map(
            fn (array $statement): array => $this->normalizeStatement($statement),
            $statements
        );

        $this->request()
            ->post($this->buildUrl((string) config('backfill.statements_path')), [
                'statements' => $normalizedStatements,
            ])
            ->throw();
    }

    /**
     * Normalize values that can legitimately differ in shape between a MySQL row fetch
     * and a PostgreSQL insert payload. The clever side should receive insert-ready rows.
     *
     * @param  array<string, mixed>  $statement
     * @return array<string, mixed>
     */
    private function normalizeStatement(array $statement): array
    {
        foreach (self::INTEGER_COLUMNS as $column) {
            if (array_key_exists($column, $statement) && $statement[$column] !== null && $statement[$column] !== '') {
                $statement[$column] = (int) $statement[$column];
            }
        }

        foreach (self::JSON_STRING_COLUMNS as $column) {
            if (array_key_exists($column, $statement)) {
                $statement[$column] = $this->normalizeJsonString($statement[$column]);
            }
        }

        foreach (self::TIMESTAMP_COLUMNS as $column) {
            if (array_key_exists($column, $statement)) {
                $statement[$column] = $this->normalizeTimestamp($statement[$column]);
            }
        }

        return $statement;
    }

    private function normalizeJsonString(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return json_encode(array_values($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function normalizeTimestamp(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            throw new RuntimeException('Backfill timestamp columns must be strings or null.');
        }

        $normalized = str_replace('T', ' ', $value);

        if (str_contains($normalized, '.')) {
            $normalized = strstr($normalized, '.', true) ?: $normalized;
        }

        return substr($normalized, 0, 19);
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
