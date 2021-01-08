<?php

use Illuminate\Database\Seeder;
use App\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!app()->environment('local')) {
            DB::disableQueryLog();
            DB::table('cities')->delete();
            $json = File::get("database/data/cities.json");
            $data = json_decode($json, true);
            City::insert($data);
        }
    }
}
