<?php

use Illuminate\Database\Seeder;
use App\State;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::disableQueryLog();
        DB::table('states')->delete();
        $json = File::get("database/data/states.json");
        $data = json_decode($json, true);
        State::insert($data);
        // foreach($data as $item){
        //     if($item['country_id']==101){
        //         State::insert($item);
        //     }
        // }
    }
}
