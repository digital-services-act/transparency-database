<?php

use App\Models\StatementAlpha;
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

            $table->string('decision_visibility', 300)->nullable();
            $table->string('decision_visibility_other', 500)->nullable();

            $table->enum('decision_monetary', array_keys(StatementAlpha::DECISION_MONETARIES))->nullable();
            $table->string('decision_monetary_other', 500)->nullable();

            $table->enum('decision_provision', array_keys(StatementAlpha::DECISION_PROVISIONS))->nullable();
            $table->enum('decision_account', array_keys(StatementAlpha::DECISION_ACCOUNTS))->nullable();
            $table->enum('account_type', array_keys(StatementAlpha::ACCOUNT_TYPES))->nullable();

            $table->enum('decision_ground', array_keys(StatementAlpha::DECISION_GROUNDS));
            $table->string('decision_ground_reference_url', 500)->nullable();

            $table->enum('category', array_keys(StatementAlpha::STATEMENT_CATEGORIES));
            $table->text('category_addition')->nullable();

            $table->text('category_specification')->nullable();
            $table->string('category_specification_other', 500)->nullable();

            $table->string('content_type', 255);
            $table->string('content_type_other', 500)->nullable();

            $table->string('illegal_content_legal_ground', 500)->nullable();

            $table->text('illegal_content_explanation')->nullable();

            $table->string('incompatible_content_ground', 500)->nullable();
            $table->text('incompatible_content_explanation')->nullable();
            $table->enum('incompatible_content_illegal', StatementAlpha::INCOMPATIBLE_CONTENT_ILLEGALS)->nullable();

            $table->string('territorial_scope', 255)->nullable();

            $table->string('content_language', 2)->nullable();
            $table->timestamp('content_date');

            $table->timestamp('application_date');

            $table->timestamp('end_date_visibility_restriction')->nullable();
            $table->timestamp('end_date_monetary_restriction')->nullable();
            $table->timestamp('end_date_service_restriction')->nullable();
            $table->timestamp('end_date_account_restriction')->nullable();

            $table->string('decision_facts', 5000);

            $table->enum('source_type', array_keys(StatementAlpha::SOURCE_TYPES));
            $table->string('source_identity', 500)->nullable();

            $table->enum('automated_detection', StatementAlpha::AUTOMATED_DETECTIONS);
            $table->enum('automated_decision', array_keys(StatementAlpha::AUTOMATED_DECISIONS));

            $table->integer('user_id');
            $table->integer('platform_id');

            $table->string('method')->default('API');

            $table->string('puid', 500);

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
