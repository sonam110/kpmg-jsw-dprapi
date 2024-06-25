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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            //$table->primary('id');
            // $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->string('name', 500);
            $table->string('email', 500);
            $table->string('password')->comment('Hash OR bcrypt');
            $table->unsignedBigInteger('role_id');
            $table->string('mobile_number')->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('avatar')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=Active,0=Inactive,2=Delete');
            $table->date('password_last_updated')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
