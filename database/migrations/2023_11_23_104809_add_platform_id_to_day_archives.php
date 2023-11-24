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
            $table->unsignedBigInteger('platform_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_archives', function (Blueprint $table) {
            $table->dropColumn(['platform_id']);
        });
    }
};
