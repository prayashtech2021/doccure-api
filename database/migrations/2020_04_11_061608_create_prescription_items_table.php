<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrescriptionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('prescription_id')->index();
            $table->foreign('prescription_id')->references('id')->on('prescriptions');

            $table->string('name');
            $table->string('qty');
            $table->string('type');
            $table->string('days');
            $table->string('time');

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
        Schema::dropIfExists('prescription_items');
    }
}
