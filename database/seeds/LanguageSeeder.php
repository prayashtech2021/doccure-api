<?php

use Illuminate\Database\Seeder;

use App\Language;

class LanguageSeeder extends Seeder
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
                'name' => 'English',
                'code' => 'En',
                'is_default' => 1,
                'created_by' => 1,
            ],
        ];
            foreach ($datas as $id => $data) {
                $row = Language::firstOrNew([
                    'id' => $id,
                ]);
                $row->fill($data);
                $row->save();
            }
    }
}
