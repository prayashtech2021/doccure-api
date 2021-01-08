<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_details', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('appointment_id')->index();
            $table->foreign('appointment_id')->references('id')->on('appointments');
            $table->integer('call_from');
            $table->integer('call_to');
            $table->string('url');
            $table->integer('start_by');
            $table->string('type');
            $table->string('channel');

            $table->integer('status')->default(1);
            $table->timestamps();
            $table->datetime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_details');
    }
}
