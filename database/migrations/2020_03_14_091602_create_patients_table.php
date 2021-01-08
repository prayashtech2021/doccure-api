<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('sex', 10);
            $table->date('dob')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('biography')->nullable();
            $table->string('where_you_heard')->nullable();

            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();

            $table->unsignedInteger('country_id')->index()->nullable();
            $table->foreign('country_id')->references('id')->on('countries');

            $table->unsignedInteger('state_id')->index()->nullable();
            $table->foreign('state_id')->references('id')->on('states');

            $table->unsignedInteger('city_id')->index()->nullable();
            $table->foreign('city_id')->references('id')->on('cities');

            $table->string('postal_code', 8)->nullable();

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
        Schema::dropIfExists('patients');
    }
}
