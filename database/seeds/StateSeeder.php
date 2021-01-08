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
        DB::table('states')->delete();
        DB::disableQueryLog();
        $json = File::get("database/data/states.json");
        $data = json_decode($json, true);
        State::insert($data);
    }
}