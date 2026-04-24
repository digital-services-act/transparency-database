<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ExportStructuralDataSql extends Command
{
    private const TABLES_IN_INSERT_ORDER = [
        'platforms',
        'day_archives',
        'users',
        'roles',
        'permissions',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',
        'personal_access_tokens',
    ];

    private const BULK_INSERT_BATCH_SIZES = [
        'day_archives' => 1000,
    ];

    private const CLEANUP_STATEMENTS = [
        'DELETE FROM "sessions";',
        'DELETE FROM "password_resets";',
        'UPDATE "api_logs" SET "platform_id" = NULL;',
    ];

    private const BOOLEAN_COLUMNS = [
        'platforms' => [
            'vlop',
            'onboarded',
            'has_tokens',
            'has_statements',
        ],
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export-structural-sql
                            {path? : Output path for the SQL file}
                            {--force : Overwrite the output file if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export structural and archive data to a PostgreSQL replayable SQL file.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->resolveOutputPath((string) $this->argument('path'));

        if (File::exists($path) && ! $this->option('force')) {
            $this->error("Output file already exists: {$path}");
            $this->line('Use --force to overwrite it.');

            return self::FAILURE;
        }

        $missingTables = collect(self::TABLES_IN_INSERT_ORDER)
            ->reject(static fn (string $table): bool => Schema::hasTable($table))
            ->values();

        if ($missingTables->isNotEmpty()) {
            $this->error('The export cannot continue because these tables are missing:');

            foreach ($missingTables as $table) {
                $this->line("- {$table}");
            }

            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $this->buildSqlDump());

        $this->info("SQL export written to {$path}");

        foreach (self::TABLES_IN_INSERT_ORDER as $table) {
            $this->line(sprintf('%s: %d row(s)', $table, DB::table($table)->count()));
        }

        return self::SUCCESS;
    }

    private function buildSqlDump(): string
    {
        $lines = [
            '-- Structural data export for transparency-database',
            '-- Generated at: ' . now()->toDateTimeString(),
            '-- Connection: ' . DB::getDefaultConnection(),
            '-- Driver: ' . DB::connection()->getDriverName(),
            '-- Target: PostgreSQL',
            '',
            '-- Initial cleanup transaction.',
            'BEGIN;',
            '',
            '-- Clear related non-exported state that can interfere with the refresh.',
            ...self::CLEANUP_STATEMENTS,
            '',
            'COMMIT;',
            '',
            '-- Refresh exported tables one table per transaction.',
        ];

        foreach (self::TABLES_IN_INSERT_ORDER as $table) {
            $lines = [
                ...$lines,
                ...$this->buildTableTransaction($table),
            ];
        }
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    private function buildTableTransaction(string $table): array
    {
        $columns = Schema::getColumnListing($table);
        $rows = $this->orderedRows($table, $columns);

        $lines = [
            '',
            sprintf('-- %s: %d row(s)', $table, $rows->count()),
            'BEGIN;',
            'DELETE FROM ' . $this->wrapIdentifier($table) . ';',
        ];

        if ($rows->isNotEmpty()) {
            $lines = [
                ...$lines,
                ...$this->buildInsertStatements($table, $columns, $rows),
            ];
        }

        if (Schema::hasColumn($table, 'id')) {
            $lines[] = $this->buildSequenceResetStatement($table);
        }

        $lines[] = 'COMMIT;';

        return $lines;
    }

    private function buildInsertStatements(string $table, array $columns, Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [];
        }

        $batchSize = self::BULK_INSERT_BATCH_SIZES[$table] ?? 1;

        if ($batchSize === 1) {
            return $this->buildSingleRowInsertStatements($table, $columns, $rows);
        }

        return $this->buildBulkInsertStatements($table, $columns, $rows, $batchSize);
    }

    private function buildSingleRowInsertStatements(string $table, array $columns, Collection $rows): array
    {
        $wrappedColumns = implode(', ', array_map(fn (string $column): string => $this->wrapIdentifier($column), $columns));
        $lines = [];

        foreach ($rows as $row) {
            $values = [];

            foreach ($columns as $column) {
                $values[] = $this->formatValue($table, $column, $row->{$column} ?? null);
            }

            $lines[] = sprintf(
                'INSERT INTO %s (%s) VALUES (%s);',
                $this->wrapIdentifier($table),
                $wrappedColumns,
                implode(', ', $values)
            );
        }

        return $lines;
    }

    private function buildBulkInsertStatements(string $table, array $columns, Collection $rows, int $batchSize): array
    {
        $wrappedColumns = implode(', ', array_map(fn (string $column): string => $this->wrapIdentifier($column), $columns));
        $lines = [];

        foreach ($rows->chunk($batchSize) as $chunk) {
            $valueTuples = [];

            foreach ($chunk as $row) {
                $values = [];

                foreach ($columns as $column) {
                    $values[] = $this->formatValue($table, $column, $row->{$column} ?? null);
                }

                $valueTuples[] = '(' . implode(', ', $values) . ')';
            }

            $lines[] = sprintf(
                "INSERT INTO %s (%s) VALUES\n%s;",
                $this->wrapIdentifier($table),
                $wrappedColumns,
                implode(",\n", $valueTuples)
            );
        }

        return $lines;
    }

    private function orderedRows(string $table, array $columns): Collection
    {
        $query = DB::table($table);
        $orderColumns = in_array('id', $columns, true) ? ['id'] : $columns;

        foreach ($orderColumns as $column) {
            $query->orderBy($column);
        }

        return $query->get();
    }

    private function formatValue(string $table, string $column, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if ($this->isBooleanColumn($table, $column)) {
            return $this->formatBooleanValue($value);
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $this->quoteString((string) $value);
    }

    private function quoteString(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    private function wrapIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    private function resolveOutputPath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return storage_path('app/exports/structural-data.sql');
        }

        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    private function buildSequenceResetStatement(string $table): string
    {
        return sprintf(
            'SELECT pg_catalog.setval(pg_get_serial_sequence(%s, %s), COALESCE(MAX(%s), 1), MAX(%s) IS NOT NULL) FROM %s;',
            $this->quoteString($table),
            $this->quoteString('id'),
            $this->wrapIdentifier('id'),
            $this->wrapIdentifier('id'),
            $this->wrapIdentifier($table)
        );
    }

    private function isBooleanColumn(string $table, string $column): bool
    {
        return in_array($column, self::BOOLEAN_COLUMNS[$table] ?? [], true);
    }

    private function formatBooleanValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 0 ? 'FALSE' : 'TRUE';
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['0', 'false', 'f', 'no', 'n', 'off', ''], true)
            ? 'FALSE'
            : 'TRUE';
    }
}
