<?php

use App\ { TimeSchedule, Appointment };
use Illuminate\Database\Seeder;

class AppointmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
    	$today_no = dayOfTheWeek(now()); // 1(sunday) to 7(saturday)
    	$todayTimeSchedules = TimeSchedule::where('days_id', $today_no)->get();

        foreach ($todayTimeSchedules as $test) {
	        $this->create_appointment( date_ymd(now()), $test );
	    }

	    $tomorrow_no = dayOfTheWeek(now()."+1 day"); // 1(sunday) to 7(saturday)
    	$tomorrowTimeSchedules = TimeSchedule::where('days_id', $tomorrow_no)->get();

	    foreach ($tomorrowTimeSchedules as $test) {
	        $this->create_appointment( date_ymd(now()."+1 day"), $test );
	    }
    }

    /**
     * Insert appointments sample data
     * @param  [type] $date [description]
     * @param  [type] $test [description]
     * @return [type]       [description]
     */
    public function create_appointment($date, $test) {
    	Appointment::create([
            'user_id' => 3, // patient id
            'schedule_id' => $test->id,
            'date' => $date
        ]);
    }
}
