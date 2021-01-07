<?php

use Illuminate\Database\Seeder;
use App\Patient;

class PatientSeeder extends Seeder
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
                'user_id' => 4,
				'first_name' => 'Saranya',
                'last_name' => 'G',
                'sex' => 'female',
                'dob' => "1995-03-31",
                'blood_group' => 'B+',
                'biography' => '',
                'where_you_heard' => '',
                'address_line1' => '',
                'country_id' => 1,
                'state_id' => 1,
                'city_id' => 1,
                'created_by' => 1,

            ],
        ];
		foreach ($datas as $id => $data) {
			$row = Patient::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
			$row->save();
		}
    }
}
