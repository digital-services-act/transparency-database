<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_archive_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('platform_id')->nullable()->constrained()->nullOnDelete();
            $table->date('archive_date')->nullable();
            $table->string('download_kind', 20);
            $table->string('file_type', 20);
            $table->string('filename', 500);
            $table->string('route_name', 100)->nullable();
            $table->char('session_hash', 64)->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['session_hash', 'created_at']);
            $table->index(['download_kind', 'created_at']);
            $table->index(['platform_id', 'archive_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_events');
    }
};
