<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('statements_beta', function (Blueprint $table): void {
            if (! Schema::hasIndex('statements_beta', 'statements_beta_created_at_id_index')) {
                $table->index(['created_at', 'id'], 'statements_beta_created_at_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('statements_beta', function (Blueprint $table): void {
            if (Schema::hasIndex('statements_beta', 'statements_beta_created_at_id_index')) {
                $table->dropIndex('statements_beta_created_at_id_index');
            }
        });
    }
};
