<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContentIdToStatementsBeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statements_beta', function (Blueprint $table) {
            $table->json('content_id')->nullable()->after('puid')
                ->comment('Optional key-value format content identifiers. Currently supports EAN-13 codes.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statements_beta', function (Blueprint $table) {
            $table->dropColumn('content_id');
        });
    }
}
