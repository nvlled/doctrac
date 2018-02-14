<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeenRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seen_routes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('routeId');
            $table->integer('userId');
            $table->string('status', 25);
            $table->unique(['routeId', 'userId', 'status']);
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
        Schema::dropIfExists('seen_routes');
    }
}
