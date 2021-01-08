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
					1
				],
            ],
            2 => [
                'name' => 'company_admin',
                'guard_name' => 'web',
                'created_by' => 1,
                'permissions' => [
					1
				],
            ],
            3 => [
                'name' => 'doctor',
                'guard_name' => 'web',
                'created_by' => 1,
                'permissions' => [
					1
				],
            ],
            4 => [
                'name' => 'patient',
                'guard_name' => 'web',
                'created_by' => 1,
                'permissions' => [
					1
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
