<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMentorDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mentor_details', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('blood_group', 225);
            $table->string('where_you_heard');
            $table->string('hourly_rate');
            $table->string('under_college');
            $table->string('under_major');
            $table->string('graduate_college');
            $table->string('degree');
            $table->text('mentor_personal_message', 65535);
            $table->decimal('mentor_charge', 10)->nullable();
            $table->string('charge_type');
            $table->string('post_college');
            $table->string('post_major');
            $table->text('business_hours')->nullable();

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
        Schema::dropIfExists('mentor_details');
    }
}
