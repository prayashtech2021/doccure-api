<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

class RoleSeeder extends Seeder
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
                'name' => 'super_admin',
                'guard_name' => 'web',
                'created_by' => 1,
                'permissions' => [
					1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26
				],
            ],
            2 => [
                'name' => 'company_admin',
                'guard_name' => 'web',
                'created_by' => 1,
                'permissions' => [
					1,4,5,6,7,8,9,10,11,12,13,14,22,23
				],
            ],
            3 => [
                'name' => 'doctor',
                'guard_name' => 'web',
                'created_by' => 1,
                'permissions' => [
					2,4,9,14,15,16,17,18,19,20,21,22,23,24
				],
            ],
            4 => [
                'name' => 'patient',
                'guard_name' => 'web',
                'created_by' => 1,
                'permissions' => [
					3,4,9,14,17,18,19,20,22,23,25,26
				],
			],
		];
		foreach ($datas as $id => $data) {
			$permissions = $data['permissions'];
			unset($data['permissions']);
			$row = Role::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
            $row->save();
            $row->permissions()->sync($permissions);
        }

        User::whereEmail('superadmin@gmail.com')->first()->assignRole('super_admin');
        User::whereEmail('admin@gmail.com')->first()->assignRole('company_admin');
        User::whereEmail('doctor@gmail.com')->first()->assignRole('doctor');
        User::whereEmail('patient@gmail.com')->first()->assignRole('patient');
    }
}
