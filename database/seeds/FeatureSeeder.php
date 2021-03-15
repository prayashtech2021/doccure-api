<?php

use Illuminate\Database\Seeder;
use App\Feature;


class FeatureSeeder extends Seeder
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
                'name' => 'Patient Ward',
                'image' => 'feature-01.jpg',
                'created_by' => 1, 
            ],
            2 => [
                'name' => 'Test Room',
                'image' => 'feature-02.jpg',
                'created_by' => 1, 
            ],
            3 => [
                'name' => 'ICU',
                'image' => 'feature-03.jpg',
                'created_by' => 1, 
            ],
            4 => [
                'name' => 'Labarotory',
                'image' => 'feature-04.jpg',
                'created_by' => 1, 
            ],
            5 => [
                'name' => 'Operation Ward',
                'image' => 'feature-05.jpg',
                'created_by' => 1, 
            ],
            6 => [
                'name' => 'Medical',
                'image' => 'feature-06.jpg',
                'created_by' => 1, 
            ],
            
        ];
            foreach ($datas as $id => $data) {
                $row = Feature::firstOrNew([
                    'id' => $id,
                ]);
                $row->fill($data);
                $row->save();
            }
    }
}
