<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_category_id');
            $table->unsignedInteger('post_sub_category_id')->nullable();
            $table->text('title');
            $table->string('slug');
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('url')->nullable();
            $table->string('banner_image');
            $table->string('thumbnail_image');
            $table->text('content');
            $table->unsignedTinyInteger('is_verified');
            $table->unsignedTinyInteger('is_viewable');
            
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by');
			$table->unsignedBigInteger('updated_by')->nullable();
			$table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('post_category_id')->references('id')->on('post_categories')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreign('post_sub_category_id')->references('id')->on('post_sub_categories')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('posts');
    }
}
