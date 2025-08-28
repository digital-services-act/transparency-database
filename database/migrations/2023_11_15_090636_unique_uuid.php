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
            //            $table->dropIndex('uuidindex');
            // Then, create a new unique index
            //            $table->unique('uuid', 'uuidindex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statements', function (Blueprint $table) {
            //            $table->dropIndex('uuidindex');
            //            $table->index('uuid', 'uuidindex');
        });
    }
};
