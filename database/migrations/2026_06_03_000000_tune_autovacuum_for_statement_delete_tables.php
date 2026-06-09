<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The prune/delete path creates many dead tuples in a short period. The
     * PostgreSQL defaults wait too long on very large tables, so tune these
     * tables to vacuum/analyze after smaller batches of churn.
     */
    public function up(): void
    {
//        if (DB::getDriverName() !== 'pgsql') {
//            return;
//        }
//
//        foreach (['statements_beta', 'platform_puids'] as $table) {
//            DB::statement(sprintf(
//                'ALTER TABLE %s SET (
//                    autovacuum_vacuum_scale_factor = 0.001,
//                    autovacuum_vacuum_threshold = 50000,
//                    autovacuum_analyze_scale_factor = 0.001,
//                    autovacuum_analyze_threshold = 50000
//                )',
//                $table
//            ));
//        }
    }

    public function down(): void
    {
//        if (DB::getDriverName() !== 'pgsql') {
//            return;
//        }
//
//        foreach (['statements_beta', 'platform_puids'] as $table) {
//            DB::statement(sprintf(
//                'ALTER TABLE %s RESET (
//                    autovacuum_vacuum_scale_factor,
//                    autovacuum_vacuum_threshold,
//                    autovacuum_analyze_scale_factor,
//                    autovacuum_analyze_threshold
//                )',
//                $table
//            ));
//        }
    }
};
