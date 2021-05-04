<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->text('first_name');
            $table->text('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('mobile_number')->unique();
            $table->string('password');
            $table->unsignedTinyInteger('gender')->default(1)->comment('1=>Male,2=>Female');
            $table->date('dob')->nullable();
            $table->char('blood_group',5)->nullable();
            $table->text('biography')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('api_token')->nullable();
            $table->rememberToken()->nullable();

            $table->unsignedTinyInteger('price_type')->comment('1=>Free,2=>Custom Price');
            $table->decimal('amount',12,2);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->char('currency_code',4)->nullable();
            $table->unsignedInteger('time_zone_id')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->comment('1=>speciality updated,0=>not updated');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->default(1);
			$table->unsignedBigInteger('updated_by')->nullable();
			$table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('time_zone_id')->references('id')->on('time_zones')->onDelete('cascade')->onUpdate('cascade');
            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
