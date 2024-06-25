<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dpr_imports', function (Blueprint $table) {
            $table->longText('sheet_json_data')->after('dpr_manage_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dpr_imports', function (Blueprint $table) {
            $table->dropColumn('sheet_json_data');
        });
    }
};
