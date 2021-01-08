<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->date('date');
            $table->unsignedInteger('schedule_id')->index();
            $table->foreign('schedule_id')->references('id')->on('time_schedules');

            $table->string('per_hour_charge')->nullable();
            $table->string('tax_percentage')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('total_hours_charge')->nullable();

            $table->string('cancelled');
            $table->string('payment_method');
            // $table->integer('invite_to');
            // $table->integer('invite_from');
            // $table->dateTime('from_date_time');
            // $table->dateTime('to_date_time');
            $table->text('message', 65535);
            // $table->date('invite_date');
            // $table->time('invite_time');
            // $table->time('invite_end_time')->nullable();
            $table->date('assign_date')->nullable();
            $table->time('assign_time')->nullable();
            $table->time('assign_end_time')->nullable();
            $table->boolean('read_status');
            $table->integer('approved')->nullable()->default(0);
            $table->boolean('paid');
            $table->integer('current_status')->default(0);
            $table->boolean('delete_sts');
            $table->string('time_zone')->nullable();
            $table->string('channel')->nullable()->default('test');

            $table->string('session_id')->nullable();
            $table->enum('type', array('online','clinic'))->nullable();
            $table->integer('booked_by')->nullable();

            $table->integer('payment_id')->nullable();
            $table->dateTime('temp_chat_date');
            $table->dateTime('expired_date');

            $table->integer('status')->default(1);

            $table->integer('created_by')->default(1);
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
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
        Schema::dropIfExists('appointments');
    }
}
