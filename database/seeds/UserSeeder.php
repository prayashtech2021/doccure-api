<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
			1 => [
				'first_name' => 'Super',
				'last_name' => 'Admin',
                'email' => 'superadmin@gmail.com',
                'password' => Hash::make('Test@123'),
                'mobile_number' => '8978675651',
                'is_verified' => 1,
                'language_id' => 1,
                'country_id' => 1,
                'created_by' => 1,
            ],
            2 => [
				'first_name' => 'Admin',
				'last_name' => '',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('Test@123'),
                'mobile_number' => '8978675654',
                'is_verified' => 1,
                'language_id' => 1,
                'country_id' => 1,
                'created_by' => 1,
            ],
            3 => [
				'first_name' => 'Doctor',
				'last_name' => '',
                'email' => 'doctor@gmail.com',
                'password' => Hash::make('Test@123'),
				'mobile_number' => '8978675655',
                'is_verified' => 1,
                'language_id' => 1,
                'country_id' => 1,
                'created_by' => 1,
            ],
            4 => [
				'first_name' => 'Patient',
				'last_name' => '',
                'email' => 'patient@gmail.com',
                'password' => Hash::make('Test@123'),
                'mobile_number' => '8978675656',
                'is_verified' => 1,
                'language_id' => 1,
                'country_id' => 1,
                'created_by' => 1,
            ],
		];
		foreach ($datas as $id => $data) {
			$row = User::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
			$row->save();
		}
    }
}
