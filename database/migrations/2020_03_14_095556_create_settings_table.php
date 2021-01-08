<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');

            $table->string('layout', 100)->nullable()->default('layout_1');
            $table->string('application_name')->nullable()->default('Infinite');
            $table->string('site_title')->nullable();
            $table->integer('slider_active')->nullable()->default(1);
            $table->string('site_color', 100)->nullable()->default('default');
            $table->string('site_lang', 100)->nullable()->default('english');
            $table->integer('show_pageviews')->nullable()->default(1);
            $table->integer('show_rss')->nullable()->default(1);
            $table->string('facebook_url', 500)->nullable();
            $table->string('twitter_url', 500)->nullable();
            $table->string('google_url', 500)->nullable();
            $table->string('instagram_url', 500)->nullable();
            $table->string('pinterest_url', 500)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('vk_url', 500)->nullable();
            $table->string('optional_url_button_name', 500)->nullable()->default('Click Here to Visit');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('about_footer', 1000)->nullable();
            $table->text('google_analytics', 65535)->nullable();
            $table->text('contact_text', 65535)->nullable();
            $table->string('contact_address', 500)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('mail_protocol', 100)->nullable()->default('smtp');
            $table->string('mail_host')->nullable();
            $table->string('mail_port')->nullable()->default('587');
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_title')->nullable();
            $table->string('primary_font')->nullable()->default('open_sans');
            $table->string('secondary_font')->nullable()->default('roboto');
            $table->string('tertiary_font')->nullable()->default('verdana');
            $table->text('facebook_comment', 65535)->nullable();
            $table->integer('pagination_per_page')->nullable()->default(6);
            $table->string('copyright', 500)->nullable()->default('Copyright ® 2018 Infinite - All Rights Reserved.');
            $table->integer('menu_limit')->nullable()->default(5);
            $table->integer('registration_system')->nullable()->default(1);
            $table->integer('comment_system')->nullable()->default(1);
            $table->text('head_code', 65535)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
