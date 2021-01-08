<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_details', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer('stripe_id')->unsigned();
            $table->foreign('stripe_id')->references('id')->on('stripe_details');

            $table->string('stripe_card_id');
            $table->string('fingerprint');
            $table->string('brand',100);
            $table->char('last4',4);
            $table->char('exp_month',2);
            $table->char('exp_year',4);
            $table->boolean('is_active')->default(1);
            $table->boolean('is_default')->default(0);
            $table->text('payload');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_details');
    }
}
