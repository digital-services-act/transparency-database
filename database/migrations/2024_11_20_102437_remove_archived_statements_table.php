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
        Schema::dropIfExists('archived_statements');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('archived_statements', static function (Blueprint $table) {
            $table->id();
            $table->integer('platform_id');
            $table->string('puid', 500);
            $table->string('uuid', 36);
            $table->bigInteger('original_id');
            $table->timestamp('date_received');
            $table->timestamps();

            $table->index(['platform_id', 'puid'], 'archived_platform_puid_index');
            $table->index('original_id', 'original_id_index');
            $table->index('uuid', 'uuid_index');

        });
    }
};
