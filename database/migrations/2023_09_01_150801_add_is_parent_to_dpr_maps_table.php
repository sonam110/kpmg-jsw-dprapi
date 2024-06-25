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
        Schema::table('dpr_maps', function (Blueprint $table) {
            $table->unsignedBigInteger('is_parent')->nullable();
            $table->foreign('is_parent')->after('row_new_position')->references('id')->on('dpr_maps')->onDelete('cascade');
            $table->integer('position')->after('is_parent')->nullable();
            $table->string('slug')->after('position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dpr_maps', function (Blueprint $table) {
            $table->dropColumn('is_parent');
            $table->dropColumn('position');
        });
    }
};
