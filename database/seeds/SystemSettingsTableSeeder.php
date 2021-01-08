<?php

use App\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$data = [
			['logo_front', ''],
			['website_name', 'dacatraonline'],
			['contact_no', '9876543210'],
			['email', 'doccure@admin.com'],
			['address', '22, Sree Kanchi Nagar, Cheran ma Nagar, Coimbatore, Tamil Nadu'],
			['zipcode', '641035'],
			['commission', '10'],
			['tax', '20']
		];

		foreach ($data as $test) {
			SystemSetting::create([
				'key' => $test[0],
				'value' => $test[1],
				'groups' => 'config'
			]);
		}
    }
}
