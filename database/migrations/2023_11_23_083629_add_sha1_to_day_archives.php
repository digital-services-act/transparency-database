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
        Schema::table('day_archives', function (Blueprint $table) {
            $table->string('sha1', 255)->nullable();
            $table->string('sha1light', 255)->nullable();
            $table->string('sha1url', 500)->nullable();
            $table->string('sha1urllight', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_archives', function (Blueprint $table) {
            $table->dropColumn([
                'sha1',
                'sha1light',
                'sha1url',
                'sha1urllight'
            ]);
        });
    }
};
