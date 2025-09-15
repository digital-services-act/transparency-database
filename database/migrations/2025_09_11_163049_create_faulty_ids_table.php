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
        Schema::create('faulty_ids', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->enum('source_table', ['statements', 'statements_beta'])->default('statements_beta')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_os_at')->nullable();
            $table->timestamp('updated_db_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faulty_ids');
    }
};
