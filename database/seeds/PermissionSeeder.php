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
            ],4 => [
				'name' => 'appointments',
				'guard_name' => 'web',
            ],5 => [
				'name' => 'specialization',
				'guard_name' => 'web',
            ],6 => [
				'name' => 'ad_doctors',
				'guard_name' => 'web',
            ],7 => [
				'name' => 'ad_patients',
				'guard_name' => 'web',
            ],8 => [
				'name' => 'payment_requests',
				'guard_name' => 'web',
            ],9 => [
				'name' => 'reviews',
				'guard_name' => 'web',
            ],10 => [
				'name' => 'settings',
				'guard_name' => 'web',
            ],11 => [
				'name' => 'email_template',
				'guard_name' => 'web',
            ],12 => [
				'name' => 'cms',
				'guard_name' => 'web',
            ],13 => [
				'name' => 'language',
				'guard_name' => 'web',
            ],14 => [
				'name' => 'my_profile',
				'guard_name' => 'web',
            ],15 => [
				'name' => 'my_patients',
				'guard_name' => 'web',
            ],16 => [
				'name' => 'schedule_timings',
				'guard_name' => 'web',
            ],17 => [
				'name' => 'calendar',
				'guard_name' => 'web',
            ],18 => [
				'name' => 'invoice',
				'guard_name' => 'web',
            ],19 => [
				'name' => 'accounts',
				'guard_name' => 'web',
            ],20 => [
				'name' => 'chat',
				'guard_name' => 'web',
            ],21 => [
				'name' => 'social_media',
				'guard_name' => 'web',
            ],22 => [
				'name' => 'change_password',
				'guard_name' => 'web',
            ],23 => [
				'name' => 'logout',
				'guard_name' => 'web',
            ],24 => [
				'name' => 'patient_search',
				'guard_name' => 'web',
            ],25 => [
				'name' => 'doctor_search',
				'guard_name' => 'web',
            ],26 => [
				'name' => 'map_grid_list',
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
