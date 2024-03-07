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
        Schema::create('archived_statements', function (Blueprint $table) {
            $table->id();
            $table->integer('platform_id');
            $table->string('puid', 500);
            $table->string('uuid', 36)->nullable();
            $table->timestamp('date_received')->nullable();
            $table->timestamps();

        //    $table->index(['platform_id', 'puid'], 'platform_puid_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_statements');
    }
};
