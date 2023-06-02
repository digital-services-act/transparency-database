<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statements', function (Blueprint $table) {
            $table->id();

            $table->string('uuid', 36)->index('uuidindex');

            $table->enum('decision_taken', array_keys(\App\Models\Statement::DECISIONS));
            $table->enum('decision_ground', array_keys(\App\Models\Statement::DECISION_GROUNDS));

            $table->enum('category', array_keys(\App\Models\Statement::SOR_CATEGORIES));

            $table->string('illegal_content_legal_ground', 255)->nullable();
            $table->string('illegal_content_explanation',500)->nullable();
            $table->string('incompatible_content_ground', 255)->nullable();
            $table->string('incompatible_content_explanation',500)->nullable();
            $table->boolean('incompatible_content_illegal')->nullable();

            $table->string('countries_list', 255)->nullable();

            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->enum('source', array_keys(\App\Models\Statement::SOURCES));
            $table->string('source_explanation', 500)->nullable();

            $table->enum('automated_detection', \App\Models\Statement::AUTOMATED_DETECTIONS);
            $table->enum('automated_decision', \App\Models\Statement::AUTOMATED_DECISIONS);
            $table->enum('automated_takedown', \App\Models\Statement::AUTOMATED_TAKEDOWNS);

            $table->integer('user_id');
            $table->string('method')->default('API');

            $table->string('url')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // disable until we need, for now sqlite can't handle this.
//            $table->fullText('illegal_content_explanation');
//            $table->fullText('incompatible_content_explanation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statements');
    }
}
