<?php

use Illuminate\Database\Seeder;

use App\Setting;

class SettingSeeder extends Seeder
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
                'slug' => 'general_settings',
                'keyword' => 'app_name',
                'value' => 'Doccure',
                'created_by' => 1,
            ],
            2 => [
                'slug' => 'general_settings',
                'keyword' => 'tax',
                'value' => '10',
                'created_by' => 1,
            ],
            3 => [
                'slug' => 'general_settings',
                'keyword' => 'commission',
                'value' => '10',
                'created_by' => 1,
            ],
            4 => [
                'slug' => 'general_settings',
                'keyword' => 'transaction_charge',
                'value' => '2',
                'created_by' => 1,
            ],
            
		];
		foreach ($datas as $id => $data) {
			$row = Setting::firstOrNew([
				'id' => $id,
			]);
			$row->fill($data);
            $row->save();
        }
    }
}
