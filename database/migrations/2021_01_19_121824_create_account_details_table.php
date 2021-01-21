<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('bank_name');
            $table->string('branch_name');
            $table->string('ifsc_code')->nullable();
            
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by');
			$table->unsignedBigInteger('updated_by')->nullable();
			$table->unsignedBigInteger('deleted_by')->nullable();

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
        Schema::dropIfExists('account_details');
    }
}
