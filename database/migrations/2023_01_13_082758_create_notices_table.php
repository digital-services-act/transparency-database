<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->longText('body')->nullable();
            $table->fullText('body');
            $table->string('language', 50);
            $table->timestamp('date_sent')->nullable();
            $table->timestamp('date_enacted')->nullable();
            $table->timestamp('date_abolished')->nullable();
            $table->string('countries_list', 255)->nullable();
            $table->enum('source', \App\Models\Notice::SOURCES)->nullable();
            $table->enum('payment_status', \App\Models\Notice::PAYMENT_STATUES)->nullable();
            $table->enum('restriction_type', \App\Models\Notice::RESTRICTION_TYPES)->nullable();
            $table->longText('restriction_type_other')->nullable();
            $table->enum('automated_detection', ["Yes","No","Partial"])->nullable();
            $table->longText('automated_detection_more')->nullable();
            $table->string('illegal_content_legal_ground', 255)->nullable();
            $table->longText('illegal_content_explanation')->nullable();
            $table->string('toc_contractual_ground', 255)->nullable();
            $table->longText('toc_explanation')->nullable();
            $table->enum('redress', \App\Models\Notice::REDRESSES)->nullable();
            $table->longText('redress_more')->nullable();
            $table->integer('user_id');
            $table->string('method')->default('API');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notices');
    }
}
