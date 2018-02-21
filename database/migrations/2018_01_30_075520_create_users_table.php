<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string("username", 150)->unique();
            $table->string("password", 255);
            $table->rememberToken();
            $table->string('firstname', 255);
            $table->string('middlename', 255)->nullable();
            $table->string('lastname', 255);
            $table->string('positionId', 255);
            $table->string('privilegeId', 255);
            $table->integer('officeId');
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
        Schema::dropIfExists('users');
    }
}

