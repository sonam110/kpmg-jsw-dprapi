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
        Schema::create('dpr_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('dpr_config_id')->nullable();
            $table->unsignedBigInteger('dpr_import_id');
            $table->foreign('dpr_import_id')->references('id')->on('dpr_imports')->onDelete('cascade');
            $table->string('import_file')->nullable();
            $table->string('original_import_file')->nullable();
            $table->date('data_date')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dpr_logs');
    }
};
