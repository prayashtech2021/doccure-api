<?php

use Illuminate\Database\Seeder;
use App\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::disableQueryLog();
        DB::table('countries')->delete();
        $json = File::get("database/data/countries.json");
        $data = json_decode($json, true);
        Country::insert($data);
    }
}
