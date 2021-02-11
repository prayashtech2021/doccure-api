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
            $table->unsignedBigInteger('appointment_id');
            $table->tinyInteger('payment_type'); // stripe or cash
            $table->string('invoice_no');
            $table->integer('duration')->comment('in seconds');
            $table->decimal('total_amount', 12, 2);
            $table->string('currency_code');
            $table->string('txn_id')->nullable();
            $table->decimal('tax', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('transaction', 12, 2);
            $table->decimal('transaction_charge', 12, 2);
            $table->text('transaction_miscellaneous');
            $table->timestamps();

            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
