<?php

use App\ { User, Speciality };
use Illuminate\Database\Seeder;

class SpecialitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// if ( $this->command->confirm("Confirm To Seed Specialities?") ) {
    		$data = [
				['Urology', 'specialities-01.png'],
				['Neurology', 'specialities-02.png'],
				['Orthopedic', 'specialities-03.png'],
				['Cardiologist', 'specialities-04.png'],
				['Dentist', 'specialities-05.png'],
			];

			foreach ($data as $test) {
				$speciality = Speciality::create([
					'name' => $test[0],
					'image' => $test[1]
				]);
			}
	    // }
    }
}
