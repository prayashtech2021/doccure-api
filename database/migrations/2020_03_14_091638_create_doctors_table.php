<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->text('profile_img', 65535)->nullable();
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->integer('mentor_gender')->default(1)->comment('( 1 - Male , 2 - Female )');
            $table->date('dob');
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();

            $table->unsignedInteger('country_id')->index()->nullable();
            $table->foreign('country_id')->references('id')->on('countries');

            $table->unsignedInteger('state_id')->index()->nullable();
            $table->foreign('state_id')->references('id')->on('states');

            $table->unsignedInteger('city_id')->index()->nullable();
            $table->foreign('city_id')->references('id')->on('cities');

            $table->string('postal_code', 8)->nullable();

            $table->text('applicant_personal_bio', 65535);
            $table->integer('is_verified')->default(0)->comment('( 0 - Not Verified , 1 - Verified )');
            $table->boolean('mobile_verified');
            $table->integer('profile_updated')->default(0)->comment('   (0 - Not Updated , 1 - Updated)');
            $table->integer('delete_sts')->default(0)->comment('(0 - Active , 1-Inactive)');
            $table->string('verification_code', 20)->nullable();
            $table->bigInteger('otp')->nullable();
            $table->string('temp_mobile_no')->nullable();

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
        Schema::dropIfExists('doctors');
    }
}
