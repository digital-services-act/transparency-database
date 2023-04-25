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


            $table->string('illegal_content_legal_ground', 255)->nullable();
            $table->string('illegal_content_explanation',500)->nullable();
            $table->string('incompatible_content_ground', 255)->nullable();
            $table->string('incompatible_content_explanation',500)->nullable();

            $table->string('countries_list', 255)->nullable();
            $table->timestamp('date_abolished')->nullable();

            $table->enum('source', array_keys(\App\Models\Statement::SOURCES));
            $table->string('source_identity', 255)->nullable();
            $table->string('source_other', 255)->nullable();

            $table->enum('automated_detection', \App\Models\Statement::AUTOMATED_DETECTIONS);
            $table->enum('automated_takedown', \App\Models\Statement::AUTOMATED_TAKEDOWNS);

            $table->enum('redress', array_keys(\App\Models\Statement::REDRESSES))->nullable();
            $table->string('redress_more', 255)->nullable();

            $table->integer('user_id');
            $table->string('method')->default('API');
            $table->timestamps();

            $table->text('statement_of_reason')->nullable();
            $table->string('url')->nullable();

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
