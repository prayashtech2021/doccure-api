<?php

use App\User;
use App\Service;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$data = [
    		'Tooth cleaning',
    		'Root Canal Therapy',
    		'Implants',
    		'Composite Bonding',
    		'Fissure Sealants',
    		'Surgical Extractions',
		];

		foreach ($data as $test) {
			$service = Service::create([
                'user_id' => '2',
				'service' => $test,
			]);
		}
    }
}
