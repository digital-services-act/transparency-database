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

            $table->enum('decision_visibility', array_keys(\App\Models\Statement::DECISIONS_VISIBILITY))->nullable();
            $table->string('decision_visibility_other', 255)->nullable();

            $table->enum('decision_monetary', array_keys(\App\Models\Statement::DECISIONS_MONETARY))->nullable();
            $table->string('decision_monetary_other', 255)->nullable();

            $table->enum('decision_provision', array_keys(\App\Models\Statement::DECISIONS_PROVISION))->nullable();
            $table->enum('decision_account', array_keys(\App\Models\Statement::DECISIONS_ACCOUNT))->nullable();

            $table->enum('decision_ground', array_keys(\App\Models\Statement::DECISION_GROUNDS));

            $table->enum('category', array_keys(\App\Models\Statement::STATEMENT_CATEGORIES));

            $table->enum('content_type', array_keys(\App\Models\Statement::CONTENT_TYPES));
            $table->string('content_type_other', 255)->nullable();

            $table->string('illegal_content_legal_ground', 255)->nullable();
            $table->string('illegal_content_explanation',500)->nullable();

            $table->string('incompatible_content_ground', 255)->nullable();
            $table->string('incompatible_content_explanation',500)->nullable();
            $table->boolean('incompatible_content_illegal')->nullable();

            $table->string('countries_list', 255)->nullable();

            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->string('decision_facts', 500);

            $table->enum('source', array_keys(\App\Models\Statement::SOURCES));

            $table->enum('automated_detection', \App\Models\Statement::AUTOMATED_DETECTIONS);
            $table->enum('automated_decision', \App\Models\Statement::AUTOMATED_DECISIONS);

            $table->integer('user_id');
            $table->string('method')->default('API');

            $table->string('url')->nullable();

            $table->timestamps();
            $table->softDeletes();

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
