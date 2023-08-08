<?php

use App\Models\Statement;
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

            $table->enum('decision_visibility', array_keys(Statement::DECISION_VISIBILITIES))->nullable();
            $table->string('decision_visibility_other', 500)->nullable();

            $table->enum('decision_monetary', array_keys(Statement::DECISION_MONETARIES))->nullable();
            $table->string('decision_monetary_other', 500)->nullable();

            $table->enum('decision_provision', array_keys(Statement::DECISION_PROVISIONS))->nullable();
            $table->enum('decision_account', array_keys(Statement::DECISION_ACCOUNTS))->nullable();
            $table->enum('account_type', array_keys(Statement::ACCOUNT_TYPES))->nullable();

            $table->enum('decision_ground', array_keys(Statement::DECISION_GROUNDS));
            $table->string('decision_ground_reference_url', 500)->nullable();

            $table->enum('category', array_keys(Statement::STATEMENT_CATEGORIES));
            $table->text('category_addition')->nullable();

            $table->text('content_type');
            $table->string('content_type_other', 500)->nullable();

            $table->string('illegal_content_legal_ground', 500)->nullable();
            $table->text('illegal_content_explanation')->nullable();

            $table->string('incompatible_content_ground', 500)->nullable();
            $table->text('incompatible_content_explanation')->nullable();
            $table->enum('incompatible_content_illegal', Statement::INCOMPATIBLE_CONTENT_ILLEGALS)->nullable();

            $table->string('territorial_scope', 255)->nullable();

            $table->timestamp('application_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->string('decision_facts', 5000);

            $table->enum('source_type', array_keys(Statement::SOURCE_TYPES));
            $table->string('source', 500)->nullable();

            $table->enum('automated_detection', Statement::AUTOMATED_DETECTIONS);
            $table->enum('automated_decision', Statement::AUTOMATED_DECISIONS);

            $table->integer('user_id');
            $table->integer('platform_id');

            $table->string('method')->default('API');

            $table->string('puid', 500);


            $table->timestamps();
            $table->softDeletes();

            $table->unique(['platform_id', 'puid']);
            $table->index('created_at');

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
