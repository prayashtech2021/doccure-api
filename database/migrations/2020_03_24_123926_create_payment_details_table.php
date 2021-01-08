<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('order_id')->unsigned();
            $table->integer('stripe_id')->unsigned();
            $table->integer('card_id')->unsigned();
            $table->string('charge_id');
            $table->string('txn_id');
            $table->float('amount');
            $table->string('currency',5);
            $table->text('payload');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('order_id')->references('id')->on('orders');
            // $table->foreign('stripe_id')->references('id')->on('user_stripe_details');
            // $table->foreign('card_id')->references('id')->on('user_card_details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_details');
    }
}
