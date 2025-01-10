<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->string('method');
            $table->foreignId('platform_id')->nullable()->constrained('platforms')->nullOnDelete();
            $table->json('request_data');
            $table->json('response_data');
            $table->integer('response_code');
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Index for faster queries
            $table->index(['platform_id', 'created_at']);
            $table->index(['endpoint', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_logs');
    }
};
