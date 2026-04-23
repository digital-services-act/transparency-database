<?php

return [
    'table' => env('BACKFILL_TABLE', 'statements_beta'),
    'start_id' => (int) env('BACKFILL_START_ID', 102107966885),
    'end_id' => (int) env('BACKFILL_END_ID', 200000000000),
];
