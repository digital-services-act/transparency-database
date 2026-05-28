<?php

namespace App\Console\Commands;

use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Throw-away benchmark for comparing the current statement indexing transform
 * with a raw pre-enriched query path.
 */
class StatementsElasticIndexBenchmark extends Command
{
    protected $signature = 'statements:elastic-index-benchmark
        {--limit=1000 : Number of statements to benchmark}
        {--date= : Select statements from a specific yyyy-mm-dd date}
        {--start-id= : Select statements from this inclusive ID}
        {--iterations=1 : Number of benchmark iterations}
        {--cold-platform-cache : Forget platform name/uuid cache keys before the current transform}
        {--compare=3 : Number of documents to compare between paths}
        {--seed-fake=0 : Create this many temporary fake statements, benchmark them, then delete them}
        {--show-sample : Print the first raw pre-enriched document as JSON}';

    protected $description = 'Benchmark current Eloquent indexing transform against a raw pre-enriched query path.';

    public function handle(StatementElasticSearchService $statement_elastic_search_service): int
    {
        $limit = $this->positiveIntOption('limit');
        $iterations = $this->positiveIntOption('iterations');
        $compare = $this->nonNegativeIntOption('compare');
        $seedFake = $this->nonNegativeIntOption('seed-fake');
        $selection = $seedFake > 0 ? $this->seedFakeStatements($seedFake) : $this->selection($limit);

        try {
            $this->line('Selection: '.$this->formatSelection($selection));
            $this->line('No Elasticsearch writes are performed; this only builds the bulk payload.');
            $this->newLine();

            $rows = [];
            $lastCurrentDocs = [];
            $lastRawDocs = [];

            for ($iteration = 1; $iteration <= $iterations; $iteration++) {
                $current = $this->runCurrentPath($selection, $statement_elastic_search_service);
                $currentBetween = $this->runCurrentPath($selection, $statement_elastic_search_service, true);
                $raw = $this->runRawPreEnrichedPath($selection, $statement_elastic_search_service);

                if ($current['rows'] === 0 && $raw['rows'] === 0) {
                    $this->warn('No statements matched the selection. Add local data or pass --date / --start-id.');

                    return self::SUCCESS;
                }

                $rows[] = $this->resultRow("current-whereIn #{$iteration}", $current);
                $rows[] = $this->resultRow("current-between #{$iteration}", $currentBetween);
                $rows[] = $this->resultRow("raw-preenriched #{$iteration}", $raw);

                $lastCurrentDocs = $current['docs'];
                $lastRawDocs = $raw['docs'];

                gc_collect_cycles();
            }

            $this->table([
                'Path',
                'Rows',
                'Fetch ms',
                'Transform ms',
                'NDJSON ms',
                'Total ms',
                'DB queries',
                'DB ms',
                'Payload MB',
                'Peak MB',
            ], $rows);

            if ($compare > 0) {
                $this->displayComparison($lastCurrentDocs, $lastRawDocs, $compare);
            }

            if ($this->option('show-sample') && isset($lastRawDocs[0])) {
                $this->newLine();
                $this->line('First raw pre-enriched document:');
                $this->line(substr(json_encode($lastRawDocs[0], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), 0, 4000));
            }
        } finally {
            if (($selection['temporary'] ?? false) === true) {
                DB::table('statements_beta')
                    ->whereBetween('id', [$selection['start_id'], $selection['end_id']])
                    ->delete();

                $this->newLine();
                $this->line(sprintf(
                    'Deleted temporary fake statements %d through %d.',
                    $selection['start_id'],
                    $selection['end_id'],
                ));
            }
        }

        return self::SUCCESS;
    }

    private function runCurrentPath(
        array $selection,
        StatementElasticSearchService $statement_elastic_search_service,
        bool $forceBetween = false,
    ): array {
        return $this->withQueryStats(function () use ($selection, $statement_elastic_search_service, $forceBetween): array {
            $fetch = $this->measure(fn (): EloquentCollection => $this->applyEloquentSelection(
                Statement::query(),
                $selection,
                $forceBetween,
            )->get());

            /** @var EloquentCollection<int, Statement> $statements */
            $statements = $fetch['value'];

            if ($this->option('cold-platform-cache')) {
                $this->forgetPlatformCacheKeys($statements->pluck('platform_id')->unique()->all());
            }

            $transform = $this->measure(function () use ($statements): array {
                $docs = [];

                /** @var Statement $statement */
                foreach ($statements as $statement) {
                    $docs[] = $statement->toSearchableArray();
                }

                return $docs;
            });

            $ndjson = $this->measure(fn (): string => $statement_elastic_search_service->buildBulkBodyFromSearchableArrays($transform['value']));

            return $this->scenarioResult($fetch, $transform, $ndjson);
        });
    }

    private function runRawPreEnrichedPath(
        array $selection,
        StatementElasticSearchService $statement_elastic_search_service,
    ): array {
        return $this->withQueryStats(function () use ($selection, $statement_elastic_search_service): array {
            $fetch = $this->measure(fn (): Collection => $this->rawStatementRowsForSelection(
                $selection,
                $statement_elastic_search_service,
            ));

            /** @var Collection<int, object> $statements */
            $statements = $fetch['value'];

            $transform = $this->measure(
                fn (): array => $statement_elastic_search_service->searchableArraysFromRawStatementRows($statements),
            );

            $ndjson = $this->measure(fn (): string => $statement_elastic_search_service->buildBulkBodyFromSearchableArrays($transform['value']));

            return $this->scenarioResult($fetch, $transform, $ndjson);
        });
    }

    private function scenarioResult(array $fetch, array $transform, array $ndjson): array
    {
        $payload = $ndjson['value'];
        $docs = $transform['value'];

        return [
            'rows' => count($docs),
            'fetch_ms' => $fetch['ms'],
            'transform_ms' => $transform['ms'],
            'ndjson_ms' => $ndjson['ms'],
            'total_ms' => $fetch['ms'] + $transform['ms'] + $ndjson['ms'],
            'payload_bytes' => strlen($payload),
            'peak_mb' => max($fetch['peak_mb'], $transform['peak_mb'], $ndjson['peak_mb']),
            'docs' => $docs,
        ];
    }

    private function withQueryStats(callable $callback): array
    {
        DB::enableQueryLog();
        DB::flushQueryLog();

        try {
            $result = $callback();
            $queries = DB::getQueryLog();
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }

        $result['db_queries'] = count($queries);
        $result['db_ms'] = array_sum(array_map(
            static fn (array $query): float => (float) ($query['time'] ?? 0),
            $queries,
        ));

        return $result;
    }

    private function measure(callable $callback): array
    {
        $start = hrtime(true);
        $value = $callback();
        $elapsed = (hrtime(true) - $start) / 1_000_000;

        return [
            'value' => $value,
            'ms' => $elapsed,
            'peak_mb' => memory_get_peak_usage(true) / 1024 / 1024,
        ];
    }

    private function applyEloquentSelection($query, array $selection, bool $forceBetween = false)
    {
        return match ($selection['type']) {
            'date' => $query
                ->where('created_at', '>=', $selection['start'])
                ->where('created_at', '<', $selection['end'])
                ->orderBy('id')
                ->limit($selection['limit']),
            'id-range' => $forceBetween
                ? $query
                    ->whereBetween('id', [$selection['start_id'], $selection['end_id']])
                    ->orderBy('id')
                : $query
                    ->whereIn('id', range($selection['start_id'], $selection['end_id'])),
            'latest' => $query
                ->orderByDesc('id')
                ->limit($selection['limit']),
            default => throw new RuntimeException('Unsupported selection type.'),
        };
    }

    private function rawStatementRowsForSelection(
        array $selection,
        StatementElasticSearchService $statement_elastic_search_service,
    ): Collection {
        return match ($selection['type']) {
            'date' => $statement_elastic_search_service->rawStatementRowsForDate($selection['start'], $selection['limit']),
            'id-range' => $statement_elastic_search_service->rawStatementRowsForIdRange($selection['start_id'], $selection['end_id']),
            'latest' => $statement_elastic_search_service->latestRawStatementRows($selection['limit']),
            default => throw new RuntimeException('Unsupported selection type.'),
        };
    }

    private function selection(int $limit): array
    {
        $date = $this->option('date');
        $startId = $this->option('start-id');

        if ($date !== null && $date !== '' && $startId !== null && $startId !== '') {
            throw new InvalidArgumentException('Use either --date or --start-id, not both.');
        }

        if ($date !== null && $date !== '') {
            $start = Carbon::createFromFormat('Y-m-d', (string) $date)->startOfDay();

            return [
                'type' => 'date',
                'start' => $start,
                'end' => $start->copy()->addDay(),
                'limit' => $limit,
            ];
        }

        if ($startId !== null && $startId !== '') {
            $startId = (int) $startId;

            if ($startId < 1) {
                throw new InvalidArgumentException('--start-id must be greater than zero.');
            }

            return [
                'type' => 'id-range',
                'start_id' => $startId,
                'end_id' => $startId + $limit - 1,
                'limit' => $limit,
            ];
        }

        return [
            'type' => 'latest',
            'limit' => $limit,
        ];
    }

    private function seedFakeStatements(int $count): array
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('--seed-fake is only allowed in local/testing environments.');
        }

        $startId = max((int) (DB::table('statements_beta')->max('id') ?? 100000000000), 100000000000) + 1;
        $createdAt = Carbon::now()->startOfDay();

        $this->line("Creating {$count} temporary fake statements...");

        Statement::factory()
            ->count($count)
            ->sequence(fn (Sequence $sequence): array => [
                'id' => $startId + $sequence->index,
                'created_at' => $createdAt->copy()->addSeconds($sequence->index)->format('Y-m-d H:i:s'),
            ])
            ->create();

        return [
            'type' => 'id-range',
            'start_id' => $startId,
            'end_id' => $startId + $count - 1,
            'limit' => $count,
            'temporary' => true,
        ];
    }

    private function formatSelection(array $selection): string
    {
        return match ($selection['type']) {
            'date' => sprintf(
                'first %d statements created on %s',
                $selection['limit'],
                $selection['start']->format('Y-m-d'),
            ),
            'id-range' => sprintf(
                'statement IDs %d through %d',
                $selection['start_id'],
                $selection['end_id'],
            ),
            'latest' => sprintf('latest %d statements by ID', $selection['limit']),
            default => 'unknown',
        };
    }

    private function resultRow(string $label, array $result): array
    {
        return [
            $label,
            number_format($result['rows']),
            number_format($result['fetch_ms'], 2),
            number_format($result['transform_ms'], 2),
            number_format($result['ndjson_ms'], 2),
            number_format($result['total_ms'], 2),
            number_format($result['db_queries']),
            number_format($result['db_ms'], 2),
            number_format($result['payload_bytes'] / 1024 / 1024, 2),
            number_format($result['peak_mb'], 2),
        ];
    }

    private function displayComparison(array $currentDocs, array $rawDocs, int $limit): void
    {
        $limit = min($limit, count($currentDocs), count($rawDocs));

        if ($limit === 0) {
            return;
        }

        $mismatches = [];

        for ($i = 0; $i < $limit; $i++) {
            $current = $this->normalizeDocument($currentDocs[$i]);
            $raw = $this->normalizeDocument($rawDocs[$i]);

            if ($current !== $raw) {
                $mismatches[] = [
                    'id' => $current['id'] ?? $raw['id'] ?? 'unknown',
                    'keys' => implode(', ', $this->mismatchedKeys($current, $raw)),
                ];
            }
        }

        $this->newLine();

        if ($mismatches === []) {
            $this->info("Compared {$limit} document(s): current and raw-preenriched payloads match after JSON normalization.");

            return;
        }

        $this->warn("Compared {$limit} document(s): found payload differences.");
        $this->table(['ID', 'Different keys'], $mismatches);
    }

    private function normalizeDocument(array $doc): array
    {
        return json_decode(json_encode($doc, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    private function mismatchedKeys(array $current, array $raw): array
    {
        $keys = array_unique(array_merge(array_keys($current), array_keys($raw)));
        sort($keys);

        return array_values(array_filter($keys, static fn (string $key): bool => ($current[$key] ?? null) !== ($raw[$key] ?? null)));
    }

    private function forgetPlatformCacheKeys(array $platformIds): void
    {
        foreach ($platformIds as $platformId) {
            Cache::forget('platform-'.$platformId.'-name');
            Cache::forget('platform-'.$platformId.'-uuid');
        }
    }

    private function positiveIntOption(string $name): int
    {
        $value = (int) $this->option($name);

        if ($value < 1) {
            throw new InvalidArgumentException(sprintf('The --%s option must be greater than zero.', $name));
        }

        return $value;
    }

    private function nonNegativeIntOption(string $name): int
    {
        $value = (int) $this->option($name);

        if ($value < 0) {
            throw new InvalidArgumentException(sprintf('The --%s option must be zero or greater.', $name));
        }

        return $value;
    }
}
