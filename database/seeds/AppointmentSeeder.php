<?php

use Illuminate\Database\Seeder;
use App\Appointment;
use App\Payment;

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

        $datas = [
			1 => [
                'appointment_id' => 1,
                'payment_type' => 3,
                'invoice_no' => 'INV234232',
                'total_amount' => '10.00',
                'currency_code' => 'USD',
                'txn_id' => 'TXN453432',
                'tax' => '10',
                'tax_amount' => '0.8',
                'transaction_charge' => '0.18',
            ],
            
		];
		foreach ($datas as $id => $data) {
			$row = Payment::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
            $row->save();
        }
    }
}
