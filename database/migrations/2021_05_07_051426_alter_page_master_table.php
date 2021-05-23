<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPageMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('page_masters', function (Blueprint $table) {
            $table->unsignedTinyInteger('type')->after('name')->default(0)->comment('0=>web,1=>Mobile');
        }); 
    }

    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('page_masters', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
