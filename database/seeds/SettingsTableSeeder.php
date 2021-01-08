<?php

use App\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		Setting::create([
			'layout' => 'layout_3',
	        'application_name' => 'Mentori.ng',
	        'site_title' => 'Mentoring',
	        'slider_active' => 1,
	        'site_color' => 'green',
	        'site_lang' => 'english',
	        'show_pageviews' => 1,
	        'show_rss' => 1,
	        'facebook_url' => '',
	        'twitter_url' => '',
	        'google_url' => '',
	        'instagram_url' => '',
	        'pinterest_url' => '',
	        'linkedin_url' => '',
	        'vk_url' => '',
	        'optional_url_button_name' => 'Click Here to Visit',
	        'logo_path' => 'assets/frontEnd/img/logo.png',
	        'favicon_path' => 'assets/frontEnd/img/favicon.png',
	        'about_footer' => 'Mentori.ng is a one-to-one tutoring application that helps students connect with the right tutor',
	        'google_analytics' => NULL,
	        'contact_text' => '',
	        'contact_address' => '',
	        'contact_email' => '',
	        'contact_phone' => '',
	        'mail_protocol' => 'smtp',
	        'mail_host' => '',
	        'mail_port' => 587,
	        'mail_username' => 'Boomi',
	        'mail_password' => 'boomi2007',
	        'mail_title' => '',
	        'primary_font' => 'open_sans',
	        'secondary_font' => 'roboto',
	        'tertiary_font' => 'verdana',
	        'facebook_comment' => '',
	        'pagination_per_page' => 6,
	        'copyright' => 'Copyright ® 2019 Mentori.ng- All Rights Reserved.',
	        'menu_limit' => 5,
	        'registration_system' => NULL,
	        'comment_system' => 1,
	        'head_code' => ''
	    ]);
    }
}
