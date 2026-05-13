<?php

return [
    'table' => env('BACKFILL_TABLE', 'statements_beta'),

    'base_url' => env('BACKFILL_BASE_URL'),
    'token' => env('BACKFILL_TOKEN'),

    'last_imported_path' => env('BACKFILL_LAST_IMPORTED_PATH', '/api/v1/backfill/last-imported-id'),
    'statements_path' => env('BACKFILL_STATEMENTS_PATH', '/api/v1/backfill/statements'),

    // This is the pre-first-record sentinel for ascending sends and the lower bound for descending sends.
    'start_id' => (int) env('BACKFILL_START_ID', 102107966885),
    'end_id' => (int) env('BACKFILL_END_ID', 200000000000),

    'chunk_size' => (int) env('BACKFILL_CHUNK_SIZE', 1000),
    // In desc mode, the target progress endpoint should return lowest_id or an equivalent last_imported_id.
    'direction' => env('BACKFILL_DIRECTION', 'desc'),
    'timeout' => (int) env('BACKFILL_TIMEOUT', 120),
    'retry_times' => (int) env('BACKFILL_RETRY_TIMES', 3),
    'retry_sleep_ms' => (int) env('BACKFILL_RETRY_SLEEP_MS', 1000),
    'queue' => env('BACKFILL_QUEUE', 'backfill'),
];
