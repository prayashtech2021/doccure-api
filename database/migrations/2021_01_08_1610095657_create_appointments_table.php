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
            $table->tinyInteger('appointment_type')->comment('1=>online,2=>clinic'); //['online','clinic']
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('next_visit')->nullable();
            $table->tinyInteger('payment_type');
            $table->text('tokbox_session_id')->nullable();
            $table->text('tokbox_token')->nullable();
            $table->boolean('payment_status')->default(true);
            $table->string('time_zone')->default('Asia\Kolkata');
            $table->unsignedTinyInteger('appointment_status')->comment('1=>new,2=>approve_request,3=>approved,4=>cancelled,5=>refund,6=>expired');
            $table->unsignedTinyInteger('request_type')->default(0)->comment('1=>payment,2=>refund');
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
