<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {

            $table->id();
            $table->string('appointment_reference')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('doctor_id');
            $table->tinyInteger('appointment_type'); //['online','clinic']
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('next_visit')->nullable();
            $table->tinyInteger('payment_type');
            $table->text('tokbox_session_id')->nullable();
            $table->text('tokbox_token')->nullable();
            $table->boolean('payment_status')->default(true);
            $table->boolean('approved_status')->default(true);
            $table->string('time_zone')->default(true);
            $table->boolean('appointment_status')->default(false);
            $table->boolean('call_status')->default(false);
            $table->boolean('review_status')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
