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
//            $table->index('category_specification');
            $table->index('decision_visibility');
            $table->index('content_type');
            $table->index('territorial_scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statements', function(Blueprint $table)
        {
//            $table->dropIndex('category_specification');
            $table->dropIndex('statements_decision_visibility_index');
            $table->dropIndex('statements_content_type_index');
            $table->dropIndex('statements_territorial_scope_index');
        });
    }
};
