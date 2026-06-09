<?php

namespace App\Services;

use App\Models\Statement;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use stdClass;
use Throwable;

class StatementElasticIndexerService
{
    public function __construct(private readonly StatementElasticConnectionService $connectionService) {}

    private function client(): Client
    {
        return $this->connectionService->client();
    }

    private function indexName(): string
    {
        return $this->connectionService->statementIndexName();
    }

    public function indexStatement(Statement $statement): string
    {
        $doc = $statement->toSearchableArray();

        $response = $this->client()->index([
            'index' => $this->indexName(),
            'id' => $statement->id,
            'body' => $doc,
            'require_alias' => true,
        ])->asArray();

        if (isset($response['result']) && in_array($response['result'], ['created', 'updated'], true)) {
            return $response['result'];
        }

        throw new RuntimeException('Elasticsearch index error');
    }

    public function bulkIndexStatements(Collection $statements): void
    {
        if ($statements->count() !== 0) {
            $docs = [];
            /** @var Statement $statement */
            foreach ($statements as $statement) {
                $docs[] = $statement->toSearchableArray();
            }

            $this->bulkSearchableArrays($docs);
        }
    }

    public function benchmarkBulkIndexStatements(Collection $statements): array
    {
        $transform = $this->measureIndexingStep(function () use ($statements): array {
            $docs = [];

            /** @var Statement $statement */
            foreach ($statements as $statement) {
                $docs[] = $statement->toSearchableArray();
            }

            return $docs;
        });

        return $this->benchmarkBulkIndexSearchableArrays($transform['value'], [
            'rows' => $statements->count(),
            'transform_ms' => $transform['ms'],
        ]);
    }

    public function bulkIndexRawStatementsForIdRange(
        int $min,
        int $max,
        bool $range = true,
        string $direction = 'asc',
    ): void {
        $this->bulkIndexRawStatementRows(
            $this->rawStatementRowsForIdRange($min, $max, $range, $direction),
        );
    }

    public function benchmarkBulkIndexRawStatementsForIdRange(
        int $min,
        int $max,
        bool $range = true,
        string $direction = 'asc',
    ): array {
        $fetch = $this->measureIndexingStep(
            fn (): SupportCollection => $this->rawStatementRowsForIdRange($min, $max, $range, $direction),
        );

        $benchmark = $this->benchmarkBulkIndexRawStatementRows($fetch['value']);
        $benchmark['fetch_ms'] = $fetch['ms'];
        $benchmark['total_ms'] += $fetch['ms'];

        return $benchmark;
    }

    public function bulkIndexRawStatementRows(SupportCollection $statements): void
    {
        if ($statements->count() !== 0) {
            $this->bulkSearchableArrays(
                $this->searchableArraysFromRawStatementRows($statements),
            );
        }
    }

    public function benchmarkBulkIndexRawStatementRows(SupportCollection $statements): array
    {
        $transform = $this->measureIndexingStep(
            fn (): array => $this->searchableArraysFromRawStatementRows($statements),
        );

        return $this->benchmarkBulkIndexSearchableArrays($transform['value'], [
            'rows' => $statements->count(),
            'transform_ms' => $transform['ms'],
        ]);
    }

    public function rawStatementRowsForIdRange(
        int $min,
        int $max,
        bool $range = true,
        string $direction = 'asc',
    ): SupportCollection {
        $direction = $this->normalizeSortDirection($direction);

        $query = $this->rawStatementRowsQuery();

        if ($range) {
            $query->whereIn('s.id', range($min, $max));
        } else {
            $query->whereBetween('s.id', [$min, $max]);
        }

        return $query
            ->orderBy('s.id', $direction)
            ->get();
    }

    public function rawStatementRowsForDate(Carbon $date, int $limit): SupportCollection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $startOfDay->copy()->addDay();

        return $this->rawStatementRowsQuery()
            ->where('s.created_at', '>=', $startOfDay)
            ->where('s.created_at', '<', $endOfDay)
            ->orderBy('s.id')
            ->limit($limit)
            ->get();
    }

    public function latestRawStatementRows(int $limit): SupportCollection
    {
        return $this->rawStatementRowsQuery()
            ->orderByDesc('s.id')
            ->limit($limit)
            ->get();
    }

    public function searchableArraysFromRawStatementRows(SupportCollection $statements): array
    {
        $docs = [];

        foreach ($statements as $statement) {
            $docs[] = $this->rawStatementRowToSearchableArray($statement);
        }

        return $docs;
    }

    public function rawStatementRowToSearchableArray(stdClass $statement): array
    {
        $decisionVisibility = $this->decodeRawArray($statement->decision_visibility);
        $contentType = $this->decodeRawArray($statement->content_type);

        return [
            'id' => (int) $statement->id,
            'decision_visibility' => $decisionVisibility,
            'decision_visibility_single' => implode('__', $decisionVisibility),
            'category_specification' => $this->decodeRawArray($statement->category_specification),
            'decision_visibility_other' => $statement->decision_visibility_other,
            'decision_monetary' => $statement->decision_monetary,
            'decision_monetary_other' => $statement->decision_monetary_other,
            'decision_provision' => $statement->decision_provision,
            'decision_account' => $statement->decision_account,
            'account_type' => $statement->account_type,
            'decision_ground' => $statement->decision_ground,
            'content_type' => $contentType,
            'content_type_single' => implode('__', $contentType),
            'content_type_other' => $statement->content_type_other,
            'content_language' => $statement->content_language,
            'illegal_content_legal_ground' => $statement->illegal_content_legal_ground,
            'illegal_content_explanation' => $statement->illegal_content_explanation,
            'incompatible_content_ground' => $statement->incompatible_content_ground,
            'incompatible_content_explanation' => $statement->incompatible_content_explanation,
            'source_type' => $statement->source_type,
            'source_identity' => $statement->source_identity,
            'decision_facts' => $statement->decision_facts,
            'automated_detection' => $statement->automated_detection === Statement::AUTOMATED_DETECTION_YES,
            'automated_decision' => $statement->automated_decision,
            'category' => $statement->category,
            'category_addition' => $this->decodeRawArray($statement->category_addition),
            'platform_id' => (int) $statement->platform_id,
            'platform_name' => $statement->platform_name ?? 'deleted-name-'.$statement->platform_id,
            'platform_uuid' => $statement->platform_uuid ?? 'deleted-uuid-'.$statement->platform_id,
            'content_date' => $this->jsonDate($statement->content_date),
            'application_date' => $this->jsonDate($statement->application_date),
            'created_at' => $this->jsonDate($statement->created_at),
            'received_date' => $this->receivedDate($statement->created_at),
            'uuid' => $statement->uuid,
            'puid' => $statement->puid,
            'territorial_scope' => $this->decodeRawArray($statement->territorial_scope),
            'method' => $statement->method,
            'content_id_ean' => $statement->content_id_ean,
        ];
    }

    public function buildBulkBodyFromSearchableArrays(array $docs): string
    {
        $bulk = [];

        foreach ($docs as $doc) {
            $bulk[] = json_encode([
                'index' => [
                    '_index' => $this->indexName(),
                    '_id' => $doc['id'],
                ],
            ], JSON_THROW_ON_ERROR);
            $bulk[] = json_encode($doc, JSON_THROW_ON_ERROR);
        }

        return $bulk === [] ? '' : implode("\n", $bulk)."\n";
    }

    private function bulkSearchableArrays(array $docs): void
    {
        $body = $this->buildBulkBodyFromSearchableArrays($docs);

        if ($body === '') {
            return;
        }

        $this->bulkWithRetry($body);
    }

    private function benchmarkBulkIndexSearchableArrays(array $docs, array $metrics): array
    {
        $ndjson = $this->measureIndexingStep(
            fn (): string => $this->buildBulkBodyFromSearchableArrays($docs),
        );
        $body = $ndjson['value'];

        $elasticMs = 0.0;
        $bulk = [
            'attempts' => 0,
            'retries' => 0,
            'retry_sleep_ms' => 0,
        ];

        if ($body !== '') {
            $elastic = $this->measureIndexingStep(fn (): array => $this->bulkWithRetry($body));
            $elasticMs = $elastic['ms'];
            $bulk = $elastic['value'];
        }

        $metrics['ndjson_ms'] = $ndjson['ms'];
        $metrics['elastic_ms'] = $elasticMs;
        $metrics['elastic_attempts'] = $bulk['attempts'];
        $metrics['elastic_retries'] = $bulk['retries'];
        $metrics['elastic_retry_sleep_ms'] = $bulk['retry_sleep_ms'];
        $metrics['payload_bytes'] = strlen($body);
        $metrics['payload_mb'] = round(strlen($body) / 1024 / 1024, 4);
        $metrics['total_ms'] = ($metrics['transform_ms'] ?? 0.0) + $metrics['ndjson_ms'] + $metrics['elastic_ms'];

        return $metrics;
    }

    private function bulkWithRetry(string $body): array
    {
        $delays = $this->bulkRetryDelaysMs();
        $maxAttempts = count($delays) + 1;
        $retrySleepMs = 0;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $this->client()->bulk([
                    'require_alias' => true,
                    'body' => $body,
                ]);

                return [
                    'attempts' => $attempt,
                    'retries' => $attempt - 1,
                    'retry_sleep_ms' => $retrySleepMs,
                ];
            } catch (Throwable $exception) {
                if (! $this->shouldRetryBulkException($exception) || $attempt >= $maxAttempts) {
                    throw $exception;
                }

                if ($exception instanceof NoNodeAvailableException) {
                    $this->connectionService->rebuildClient();
                }

                $delayMs = $this->jitteredBulkRetryDelayMs($delays[$attempt - 1]);
                $retrySleepMs += $delayMs;

                Log::warning('Elasticsearch bulk retry', [
                    'attempt' => $attempt,
                    'next_attempt' => $attempt + 1,
                    'max_attempts' => $maxAttempts,
                    'delay_ms' => $delayMs,
                    'exception' => get_class($exception),
                    'status' => $this->bulkExceptionStatus($exception),
                    'message' => $exception->getMessage(),
                    'payload_mb' => round(strlen($body) / 1024 / 1024, 4),
                ]);

                usleep($delayMs * 1000);
            }
        }

        throw new RuntimeException('Elasticsearch bulk retry loop exited unexpectedly.');
    }

    private function shouldRetryBulkException(Throwable $exception): bool
    {
        if ($exception instanceof NoNodeAvailableException || $exception instanceof ServerResponseException) {
            return true;
        }

        if ($exception instanceof ClientResponseException) {
            return in_array($this->bulkExceptionStatus($exception), [408, 429], true);
        }

        return false;
    }

    private function bulkExceptionStatus(Throwable $exception): ?int
    {
        if (! method_exists($exception, 'getResponse')) {
            return null;
        }

        try {
            return $exception->getResponse()->getStatusCode();
        } catch (Throwable) {
            return null;
        }
    }

    private function bulkRetryDelaysMs(): array
    {
        $delays = config('elasticsearch.bulk_retry_delays_ms', [250, 750, 1500, 3000]);

        if (! is_array($delays)) {
            $delays = [$delays];
        }

        return array_values(array_filter(
            array_map(static fn ($delay): int => max(0, (int) $delay), $delays),
            static fn (int $delay): bool => $delay > 0,
        ));
    }

    private function jitteredBulkRetryDelayMs(int $delayMs): int
    {
        return max(1, $delayMs + random_int(0, max(1, (int) floor($delayMs / 5))));
    }

    private function measureIndexingStep(callable $callback): array
    {
        $start = hrtime(true);
        $value = $callback();

        return [
            'value' => $value,
            'ms' => round((hrtime(true) - $start) / 1_000_000, 3),
        ];
    }

    private function rawStatementRowsQuery(): QueryBuilder
    {
        return DB::table('statements_beta as s')
            ->leftJoin('platforms as p', function ($join): void {
                $join->on('p.id', '=', 's.platform_id')
                    ->whereNull('p.deleted_at');
            })
            ->whereNull('s.deleted_at')
            ->select($this->rawStatementSelectColumns());
    }

    private function rawStatementSelectColumns(): array
    {
        return [
            's.id',
            's.uuid',
            's.decision_visibility',
            's.decision_visibility_other',
            's.decision_monetary',
            's.decision_monetary_other',
            's.decision_provision',
            's.decision_account',
            's.account_type',
            's.decision_ground',
            's.content_type',
            's.content_type_other',
            's.content_language',
            's.illegal_content_legal_ground',
            's.illegal_content_explanation',
            's.incompatible_content_ground',
            's.incompatible_content_explanation',
            's.source_type',
            's.source_identity',
            's.decision_facts',
            's.automated_detection',
            's.automated_decision',
            's.category',
            's.category_addition',
            's.category_specification',
            's.platform_id',
            'p.name as platform_name',
            'p.uuid as platform_uuid',
            's.content_date',
            's.application_date',
            's.created_at',
            's.puid',
            's.territorial_scope',
            's.method',
            's.content_id_ean',
        ];
    }

    private function decodeRawArray(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        $decoded = array_unique($decoded);
        sort($decoded);

        return array_values($decoded);
    }

    private function jsonDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->toJSON();
    }

    private function receivedDate(?string $createdAt): ?string
    {
        if ($createdAt === null || $createdAt === '') {
            return null;
        }

        return Carbon::parse($createdAt)->startOfDay()->toJSON();
    }

    private function normalizeSortDirection(string $direction): string
    {
        $direction = strtolower($direction);

        if (! in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Raw statement sort direction must be asc or desc.');
        }

        return $direction;
    }
}
