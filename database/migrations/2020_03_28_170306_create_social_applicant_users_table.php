<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialApplicantUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_applicant_users', function (Blueprint $table) {
            $table->increments('id');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('username', 50)->nullable();

            $table->string('email');
            $table->string('gender', 10)->nullable();
            $table->string('locale', 10)->nullable();
            $table->string('picture_url')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('register_type')->nullable();

            $table->integer('reference_id');
            $table->string('oauth_provider');
            $table->string('oauth_uid');

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
        Schema::dropIfExists('social_applicant_users');
    }
}
