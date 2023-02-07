<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityStatementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_statement', function (Blueprint $table) {
            $table->foreignId('entity_id')->onDelete('cascade');
            $table->foreignId('statement_id')->onDelete('cascade');
            $table->enum('role', ["principal", "agent", "recipient", "sender", "target", "issuing_court", "plaintiff", "defendant","submitter"])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_statement');
    }
}
