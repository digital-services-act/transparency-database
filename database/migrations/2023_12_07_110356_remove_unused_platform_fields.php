<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('platforms', 'url')) {
            Schema::dropColumns('platforms', 'url');
        }
        if (Schema::hasColumn('platforms', 'icon_file_name')) {
            Schema::dropColumns('platforms', 'icon_file_name');
        }
        if (Schema::hasColumn('platforms', 'icon_file_size')) {
            Schema::dropColumns('platforms', 'icon_file_size');
        }
        if (Schema::hasColumn('platforms', 'icon_content_type')) {
            Schema::dropColumns('platforms', 'icon_content_type');
        }
        if (Schema::hasColumn('platforms', 'icon_updated_at')) {
            Schema::dropColumns('platforms', 'icon_updated_at');
        }
        if (Schema::hasColumn('platforms', 'registration_id')) {
            Schema::dropColumns('platforms', 'registration_id');
        }
        if (Schema::hasColumn('platforms', 'dsa_common_id')) {
            Schema::dropColumns('platforms', 'dsa_common_id');
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
