<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBitInteger('user_id');
            $table->string('code');
            $table->text('description');
            $table->char('currency_code',5);
            $table->unsignedTinyInteger('request_type')->comment('1=>Payment,2=>Refund');
            $table->decimal('request_amount',12,2);
            $table->unsignedTinyInteger('status')->comment('1=>New,2=>Approved,3=>Paid,4=>Rejected');
            $table->dateTime('action_date')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->nullable();
            $table->softDeletes();
            $table->unsignedBitInteger('created_by');
			$table->unsignedBitInteger('updated_by')->nullable();
			$table->unsignedBitInteger('deleted_by')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_requests');
    }
}
