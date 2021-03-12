<?php

use Illuminate\Database\Seeder;

use App\ScheduleTiming;

class ScheduleTimingSeeder extends Seeder
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
                'provider_id' => 3,
                'appointment_type' => 1,
                // 'duration' => '600',
                'working_hours' => '{"sunday":[],"monday":["09:00-09:10","13:00-13:10"],"tuesday":["09:00-09:10","13:00-13:10"],"wednesday":["09:00-09:10","13:00-13:10"],"thursday":["09:00-09:10","13:00-13:10"],"friday":["09:00-09:10","13:00-13:10"],"saturday":["09:00-09:10"]}',
            ],
            2 => [
                'provider_id' => 3,
                'appointment_type' => 2,
                // 'duration' => '600',
                'working_hours' => '{"sunday":[],"monday":["11:00-11:10","18:00-18:10"],"tuesday":["11:00-11:10","18:00-18:10"],"wednesday":["11:00-11:10","18:00-18:10"],"thursday":["11:00-11:10","18:00-18:10"],"friday":["11:00-11:10:10","18:00-18:10"],"saturday":["11:00-11:10"]}',
            ],
        ];
		foreach ($datas as $id => $data) {
			$row = ScheduleTiming::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
            $row->save();
        }
    }
}
