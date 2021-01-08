<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('doctor_id');
            $table->string('invoice_no');
            $table->decimal('per_hour_charge', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('currency_code');
            $table->tinyInteger('payment_type'); // stripe
            $table->string('txn_id');
            $table->decimal('tax', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->string('transaction_charge');
            $table->text('transaction_miscellaneous');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
