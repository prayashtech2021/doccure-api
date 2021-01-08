<?php

use App\TimeSchedule;
use Illuminate\Database\Seeder;

class TimeSchedulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$data = [
			['17:40:00', '17:45:00', '1', 'Sunday', 'online', '5'],
			['17:45:00', '17:50:00', '1', 'Sunday', 'clinic', '5'],
			['14:30:00', '14:40:00', '2', 'Monday', 'online', '10'],
			['14:40:00', '14:50:00', '2', 'Monday', 'clinic', '10'],
			['12:20:00', '12:35:00', '3', 'Tuesday', 'online', '15'],
			['12:35:00', '12:50:00', '3', 'Tuesday', 'clinic', '15'],
			['10:00:00', '10:20:00', '4', 'Wednesday', 'online', '20'],
			['10:30:00', '10:50:00', '4', 'Wednesday', 'clinic', '20'],
			['08:00:00', '08:30:00', '5', 'Thursday', 'online', '30'],
			['08:30:00', '09:00:00', '5', 'Thursday', 'clinic', '30'],
			['06:00:00', '06:45:00', '6', 'Friday', 'online', '45'],
			['06:45:00', '07:30:00', '6', 'Friday', 'clinic', '45'],
			['20:00:00', '21:00:00', '7', 'Saturday', 'online', '1'],
			['21:00:00', '22:00:00', '7', 'Saturday', 'clinic', '1'],
		];

		foreach ($data as $test) {
	        TimeSchedule::create([
	            'user_id' => 2,
	            'from_time' => $test[0],
	            'to_time' => $test[1],
	            'time_zone' => 'Asia/Kolkata',
	            'days_id' => $test[2],
	            'day_name' => $test[3],
	            'type' => $test[4],
	            'slot' => $test[5],
	        ]);
	    }
    }
}
