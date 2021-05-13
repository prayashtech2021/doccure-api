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
            DB::disableQueryLog();
            DB::table('cities')->delete();
            $json = File::get("database/data/cities.json");
            $data = json_decode($json, true);
            // City::insert($data);
            foreach($data as $item){
                //if($item['country_id']==101 && $item['state_id']==4035){
                    City::insert($item);
                //}
            }
    }
}
