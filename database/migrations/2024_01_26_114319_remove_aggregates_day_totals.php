<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('application_date_aggregates');
        Schema::dropIfExists('content_date_aggregates');
        Schema::dropIfExists('sql_aggregates');
        //        Schema::dropIfExists('platform_day_totals');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
