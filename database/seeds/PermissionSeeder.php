<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
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
				'name' => 'admin_dashboard',
				'guard_name' => 'web',
            ],
            2 => [
				'name' => 'doctor_dashboard',
				'guard_name' => 'web',
            ],
            3 => [
				'name' => 'patient_dashboard',
				'guard_name' => 'web',
            ],
		];
		foreach ($datas as $id => $data) {
			$row = Permission::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
			$row->save();
		}
    }
}
