<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('day_archives', function (Blueprint $table) {
            $table->unsignedBigInteger('parquet_size')->nullable();
            $table->string('parquet_sha1', 40)->nullable();
            $table->string('parquet_url', 500)->nullable();
            $table->string('parquet_sha1_url', 500)->nullable();
            $table->unsignedBigInteger('total_parquet')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('day_archives', function (Blueprint $table) {
            $table->dropColumn([
                'parquet_size',
                'parquet_sha1',
                'parquet_url',
                'parquet_sha1_url',
                'total_parquet',
            ]);
        });
    }
};
