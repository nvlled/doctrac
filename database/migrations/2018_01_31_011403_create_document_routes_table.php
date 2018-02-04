<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_routes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("pathId");
            $table->string('trackingId', 512);
            $table->integer('officeId');
            $table->integer('userId')->nullable();
            $table->integer('nextId')->nullable();
            $table->integer('prevId')->nullable();
            $table->dateTime('arrivalTime')->nullable();
            $table->text('annotations')->nullable();
            $table->boolean('final')->default(false);
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
        Schema::dropIfExists('document_routes');
    }
}
