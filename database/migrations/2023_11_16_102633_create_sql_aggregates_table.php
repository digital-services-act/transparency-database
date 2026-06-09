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
        Schema::create('sql_aggregates', function (Blueprint $table) {
            $table->id();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('aggregate_type')->default('HOURLY');
            $table->string('sor_count');
            $table->integer('platform_id');
            $table->string('category')->nullable();
            $table->string('decision_ground')->nullable();
            $table->string('decision_account')->nullable();
            $table->string('decision_monetary')->nullable();
            $table->string('decision_provision')->nullable();
            $table->string('automated_detection');
            $table->string('automated_decision');
            $table->string('content_type');
            $table->string('source_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sql_aggregates');
    }
};
