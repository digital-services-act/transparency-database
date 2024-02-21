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
        Schema::table('statements', function (Blueprint $table) {
            if (Schema::hasIndex('statements', 'statements_decision_visibility_index')) {
                $table->dropIndex('statements_decision_visibility_index');
            }

            if (Schema::hasIndex('statements', 'statements_content_type_index')) {
                $table->dropIndex('statements_content_type_index');
            }

            if (Schema::hasIndex('statements', 'statements_territorial_scope_index')) {
                $table->dropIndex('statements_territorial_scope_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
