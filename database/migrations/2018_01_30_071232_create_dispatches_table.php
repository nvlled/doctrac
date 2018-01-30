<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trackingId', 512);
            $table->dateTime('timeSent')->nullable();
            $table->dateTime('timeSeen')->nullable();
            $table->dateTime('timeRecv')->nullable();
            $table->integer('srcLocId');
            $table->integer('dstLocId')->nullable();
            $table->integer('srcUserId')->nullable();
            $table->integer('dstUserId')->nullable();
            $table->text('annotations')->nullable();
            $table->boolean('done')->default('false');
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
        Schema::dropIfExists('dispatches');
    }
}
