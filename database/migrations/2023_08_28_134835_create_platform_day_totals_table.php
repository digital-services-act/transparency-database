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
        Schema::create('platform_day_totals', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('platform_id');
            $table->string('attribute')->default('*');
            $table->string('value')->default('*');
            $table->unsignedInteger('total')->default(0);
            $table->timestamps();

            $table->foreign('platform_id')->references('id')->on('platforms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_day_totals');
    }
};
