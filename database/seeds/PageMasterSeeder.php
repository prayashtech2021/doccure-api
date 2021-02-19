<?php

use Illuminate\Database\Seeder;

use App\PageMaster;

class PageMasterSeeder extends Seeder
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
                'name' => 'menu',
            ],
            2 => [
                'name' => 'landing_page',
            ],
            3 => [
                'name' => 'doctor_search',
            ],
            4 => [
                'name' => 'doctor_preview',
            ],
            5 => [
                'name' => 'login',
            ],
            6 => [
                'name' => 'register',
            ],
        ];
            foreach ($datas as $id => $data) {
                $row = PageMaster::firstOrNew([
                    'id' => $id,
                ]);
                $row->fill($data);
                $row->save();
            }
    }
}
