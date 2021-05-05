<?php

use Illuminate\Database\Seeder;
use App\Banner;

class BannerSeeder extends Seeder
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
                'name' => 'Visit a Doctor',
                'button_name' => 'Book Now',
                'link' => '',
                'image' => 'looking_for_1.jpg',
                'created_by' => 1, 
            ],
            2 => [
                'name' => 'Find a Pharmacy',
                'button_name' => 'Coming Soon',
                'link' => '',
                'image' => 'looking_for_2.jpg',
                'created_by' => 1, 
            ],
            3 => [
                'name' => 'Find a Lab',
                'button_name' => 'Coming Soon',
                'link' => '',
                'image' => 'looking_for_3.jpg',
                'created_by' => 1, 
            ],
        ];
            foreach ($datas as $id => $data) {
                $row = Banner::firstOrNew([
                    'id' => $id,
                ]);
                $row->fill($data);
                $row->save();
            }
    }
}
