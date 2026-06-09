<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
//        if (DB::getDriverName() === 'pgsql') {
//            DB::statement(
//                'CREATE INDEX CONCURRENTLY IF NOT EXISTS platform_puids_created_at_id_index ON platform_puids (created_at, id)'
//            );
//
//            return;
//        }
//
//        Schema::table('platform_puids', function (Blueprint $table): void {
//            if (! Schema::hasIndex('platform_puids', 'platform_puids_created_at_id_index')) {
//                $table->index(['created_at', 'id'], 'platform_puids_created_at_id_index');
//            }
//        });
    }

    public function down(): void
    {
//        if (DB::getDriverName() === 'pgsql') {
//            DB::statement('DROP INDEX CONCURRENTLY IF EXISTS platform_puids_created_at_id_index');
//
//            return;
//        }
//
//        Schema::table('platform_puids', function (Blueprint $table): void {
//            if (Schema::hasIndex('platform_puids', 'platform_puids_created_at_id_index')) {
//                $table->dropIndex('platform_puids_created_at_id_index');
//            }
//        });
    }
};
