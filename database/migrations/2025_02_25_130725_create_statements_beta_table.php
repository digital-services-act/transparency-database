<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatementsBetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statements_beta', function (Blueprint $table): void {
            $table->id()->startingValue(100000000000);

            $table->string('uuid', 36);

            $table->string('decision_visibility', 300)->nullable();
            $table->string('decision_visibility_other', 500)->nullable();

            $table->string('decision_monetary', 100)->nullable();
            $table->string('decision_monetary_other', 500)->nullable();

            $table->string('decision_provision', 100)->nullable();
            $table->string('decision_account', 100)->nullable();
            $table->string('account_type', 100)->nullable();

            $table->string('decision_ground', 100);
            $table->string('decision_ground_reference_url', 500)->nullable();

            $table->string('category', 100);
            $table->text('category_addition')->nullable();

            $table->text('category_specification')->nullable();
            $table->string('category_specification_other', 500)->nullable();

            $table->string('content_type', 255);
            $table->string('content_type_other', 500)->nullable();

            $table->string('illegal_content_legal_ground', 500)->nullable();

            $table->text('illegal_content_explanation')->nullable();

            $table->string('incompatible_content_ground', 500)->nullable();
            $table->text('incompatible_content_explanation')->nullable();
            $table->string('incompatible_content_illegal', 100)->nullable();

            $table->string('territorial_scope', 255)->nullable();

            $table->string('content_language', 2)->nullable();
            $table->timestamp('content_date');

            $table->timestamp('application_date');

            $table->timestamp('end_date_visibility_restriction')->nullable();
            $table->timestamp('end_date_monetary_restriction')->nullable();
            $table->timestamp('end_date_service_restriction')->nullable();
            $table->timestamp('end_date_account_restriction')->nullable();

            $table->string('decision_facts', 5000);

            $table->string('source_type', 100);
            $table->string('source_identity', 500)->nullable();

            $table->string('automated_detection', 100);
            $table->string('automated_decision', 100);

            $table->integer('user_id');
            $table->integer('platform_id');

            $table->string('method')->default('API');

            $table->string('puid', 500);


            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_at'], 'created_at_index');
            $table->index(['uuid'], 'uuid_index');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statements_beta');
    }
}
