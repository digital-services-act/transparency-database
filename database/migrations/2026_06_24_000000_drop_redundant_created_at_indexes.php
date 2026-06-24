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
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX CONCURRENTLY IF EXISTS platform_puid_created_at_index');
            DB::statement('DROP INDEX CONCURRENTLY IF EXISTS created_at_index');

            return;
        }

        Schema::table('platform_puids', function (Blueprint $table): void {
            if (Schema::hasIndex('platform_puids', 'platform_puid_created_at_index')) {
                $table->dropIndex('platform_puid_created_at_index');
            }
        });

        Schema::table('statements_beta', function (Blueprint $table): void {
            if (Schema::hasIndex('statements_beta', 'created_at_index')) {
                $table->dropIndex('created_at_index');
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS platform_puid_created_at_index ON platform_puids (created_at)');
            DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS created_at_index ON statements_beta (created_at)');

            return;
        }

        Schema::table('platform_puids', function (Blueprint $table): void {
            if (! Schema::hasIndex('platform_puids', 'platform_puid_created_at_index')) {
                $table->index(['created_at'], 'platform_puid_created_at_index');
            }
        });

        Schema::table('statements_beta', function (Blueprint $table): void {
            if (! Schema::hasIndex('statements_beta', 'created_at_index')) {
                $table->index(['created_at'], 'created_at_index');
            }
        });
    }
};
