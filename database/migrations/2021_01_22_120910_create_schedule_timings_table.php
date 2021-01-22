<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleTimingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_timings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->comment('service provider,user_id'); //user id
            $table->unsignedTinyInteger('appointment_type')->comment('1=>online,2=>offline');
            $table->string('duration')->comment('in seconds');
            $table->json('working_hours');

            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_timings');
    }
}
