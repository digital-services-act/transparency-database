<?php

use App\Models\Workflow;
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
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
//            $table->unsignedBigInteger('workflow_id');
//            $table->foreign('workflow_id')->references('id')->on('workflows');
            $table->foreignIdFor(Workflow::class);
            $table->unsignedBigInteger('current_step')->nullable();
            $table->foreign('current_step')->references('id')->on('workflow_steps');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_instances');
    }
};
