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
            $table->string('decision_visibility', 300)->change();
            $table->string('content_type', 255)->change();
            $table->string('territorial_scope', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statements', function(Blueprint $table)
        {
        });
    }
};
