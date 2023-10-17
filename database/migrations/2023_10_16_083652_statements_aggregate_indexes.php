<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('statements', function(Blueprint $table)
        {
            $table->index([
                'platform_id',
                'decision_visibility',
                'decision_monetary',
                'decision_provision',
                'decision_account',
                'category',
                'decision_ground',
                'automated_detection',
                'automated_decision',
                'content_type',
                'source_type',
                'content_date'
            ], 'content_date_aggregate_index');

            $table->index([
                'platform_id',
                'decision_visibility',
                'decision_monetary',
                'decision_provision',
                'decision_account',
                'category',
                'decision_ground',
                'automated_detection',
                'automated_decision',
                'content_type',
                'source_type',
                'application_date'
            ], 'application_date_aggregate_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statements', function(Blueprint $table) {
            $table->dropIndex('content_date_aggregate_index');
            $table->dropIndex('application_date_aggregate_index');
        });
    }
};
