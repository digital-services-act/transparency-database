<?php
//
//use Illuminate\Database\Migrations\Migration;
//use Illuminate\Database\Schema\Blueprint;
//use Illuminate\Support\Facades\Schema;
//
//return new class extends Migration
//{
//    /**
//     * Run the migrations.
//     */
//    public function up(): void
//    {
//        Schema::create('database_velocities', function (Blueprint $table) {
//            $table->id();
//            $table->unsignedBigInteger('max_statement_id');
//            $table->decimal('rows_per_second', 10, 2);
//            $table->timestamps();
//        });
//    }
//
//    /**
//     * Reverse the migrations.
//     */
//    public function down(): void
//    {
//        Schema::dropIfExists('database_velocities');
//    }
//};
