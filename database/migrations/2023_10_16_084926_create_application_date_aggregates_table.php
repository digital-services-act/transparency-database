<?php

use App\Models\Statement;
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
        Schema::create('application_date_aggregates', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('platform_id');
            $table->string('platform_name', 255);

            $table->date('date');

            $table->string('decision_visibility',300)->nullable();
            $table->enum('decision_monetary', array_keys(Statement::DECISION_MONETARIES))->nullable();
            $table->enum('decision_provision', array_keys(Statement::DECISION_PROVISIONS))->nullable();
            $table->enum('decision_account', array_keys(Statement::DECISION_ACCOUNTS))->nullable();
            $table->enum('category', array_keys(Statement::STATEMENT_CATEGORIES));
            $table->enum('decision_ground', array_keys(Statement::DECISION_GROUNDS));
            $table->enum('automated_detection', Statement::AUTOMATED_DETECTIONS);
            $table->enum('automated_decision', array_keys(Statement::AUTOMATED_DECISIONS));
            $table->string('content_type', 255);
            $table->enum('source_type', array_keys(Statement::SOURCE_TYPES));

            $table->unsignedInteger('total')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_date_aggregates');
    }
};
