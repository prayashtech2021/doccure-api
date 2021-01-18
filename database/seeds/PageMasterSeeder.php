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
