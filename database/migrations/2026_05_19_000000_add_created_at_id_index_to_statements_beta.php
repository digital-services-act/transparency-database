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
//                'CREATE INDEX CONCURRENTLY IF NOT EXISTS statements_beta_created_at_id_index ON statements_beta (created_at, id)'
//            );
//
//            return;
//        }
//
//        Schema::table('statements_beta', function (Blueprint $table): void {
//            if (! Schema::hasIndex('statements_beta', 'statements_beta_created_at_id_index')) {
//                $table->index(['created_at', 'id'], 'statements_beta_created_at_id_index');
//            }
//        });
    }

    public function down(): void
    {
//        if (DB::getDriverName() === 'pgsql') {
//            DB::statement('DROP INDEX CONCURRENTLY IF EXISTS statements_beta_created_at_id_index');
//
//            return;
//        }
//
//        Schema::table('statements_beta', function (Blueprint $table): void {
//            if (Schema::hasIndex('statements_beta', 'statements_beta_created_at_id_index')) {
//                $table->dropIndex('statements_beta_created_at_id_index');
//            }
//        });
    }
};
