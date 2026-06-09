<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove duplicate entries keeping only the oldest one (works on both MySQL and SQLite)
        $duplicateIds = DB::table('platform_puids as p1')
            ->join('platform_puids as p2', function ($join) {
                $join->on('p1.platform_id', '=', 'p2.platform_id')
                    ->on('p1.puid', '=', 'p2.puid')
                    ->whereColumn('p1.id', '>', 'p2.id');
            })
            ->pluck('p1.id');

        if ($duplicateIds->isNotEmpty()) {
            DB::table('platform_puids')->whereIn('id', $duplicateIds)->delete();
        }

        Schema::table('platform_puids', function (Blueprint $table) {
            // Drop the existing non-unique index
            $table->dropIndex('platform_puid_index');

            // Add unique constraint for insertOrIgnore to work correctly
            $table->unique(['platform_id', 'puid'], 'platform_puid_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_puids', function (Blueprint $table) {
            $table->dropUnique('platform_puid_unique');
            $table->index(['platform_id', 'puid'], 'platform_puid_index');
        });
    }
};
