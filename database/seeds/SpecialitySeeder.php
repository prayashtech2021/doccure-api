<?php

use Illuminate\Database\Seeder;
use App\Speciality;
use App\UserSpeciality;

class SpecialitySeeder extends Seeder
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
                'name' => 'Skin care',
                'duration' => 600,
                'amount' => 10,
                'created_by' => 1,
            ],
            
		];
		foreach ($datas as $id => $data) {
			$row = Speciality::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
            $row->save();
        }

        $datas = [
			1 => [
                'user_id' => 3,
                'speciality_id' => 1,
                'duration' => 600,
                'amount' => 200,
                'created_by' => 1,
            ],
            
		];
		foreach ($datas as $id => $data) {
			$row = new UserSpeciality;
			$row->fill($data);
            $row->save();
        }
    }
}
