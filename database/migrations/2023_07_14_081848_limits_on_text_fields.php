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
            $table->string('url', 500)->change();
            $table->string('illegal_content_legal_ground', 500)->change();
            $table->string('incompatible_content_ground', 500)->change();
            $table->string('source', 500)->change();
            $table->string('decision_visibility_other', 500)->change();
            $table->string('decision_monetary_other', 500)->change();
            $table->string('content_type_other', 500)->change();

            $table->string('illegal_content_explanation', 2000)->change();
            $table->string('incompatible_content_explanation', 2000)->change();

            $table->string('decision_facts', 5000)->change();
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
