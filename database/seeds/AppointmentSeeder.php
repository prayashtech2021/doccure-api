<?php

use Illuminate\Database\Seeder;
use App\Appointment;

class AppointmentSeeder extends Seeder
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
                'appointment_reference' => 'APT00401',
                'user_id' => 4,
                'doctor_id' => 3,
                'appointment_type' => 1,
                'appointment_date' => date('Y-m-d'),
                'start_time' => '14:00:00',
                'end_time' => '14:30:00',
                'payment_type' => 3,
                'payment_status' => 1,
                'appointment_status' => 1,
            ],
            
		];
		foreach ($datas as $id => $data) {
			$row = Appointment::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
            $row->save();
        }
    }
}
