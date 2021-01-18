<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_code')->after('profile_image')->nullable(); 
            $table->unsignedTinyInteger('is_verified')->after('verification_code')->default(0);
            $table->unsignedBigInteger('language_id')->after('time_zone_id')->default(1);
            // $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade')->onUpdate('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('country_id');
            $table->dropForeign('language_id');
            $table->dropColumn('verification_code');
            $table->dropColumn('is_verified');
            $table->dropColumn('language_id');
        });
    }
}
