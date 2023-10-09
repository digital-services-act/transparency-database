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
        Schema::create('day_archives', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('url', 500)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->bigInteger('total')->default(0)->unsigned();
            $table->timestamps();
            $table->unique(['date'], 'date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_archives');
    }
};
