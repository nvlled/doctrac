<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficeMobileNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('office_mobile_numbers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('officeId');
            $table->string('data');
            $table->string('name')->nullable();
            $table->unique(['officeId', 'data']);
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
        Schema::dropIfExists('office_mobile_numbers');
    }
}
