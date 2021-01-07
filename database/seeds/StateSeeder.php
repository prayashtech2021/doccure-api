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
        //
        $data = [
            [1, 'Andaman and Nicobar Islands', 1],
            [2, 'Andhra Pradesh', 1],
            [3, 'Arunachal Pradesh', 2],
            [4, 'Assam', 2],
            [5, 'Bihar', 3],
            [6, 'Chandigarh', 3],
            [7, 'Chhattisgarh', 4],
            [8, 'Dadra and Nagar Haveli', 4],
            [9, 'Daman and Diu', 5],
            [10, 'Delhi', 5],
        ];

        foreach ($data as $test) {
	        State::create([
	            'name' => $test[1],
	            'country_id' => $test[2],
	        ]);
	    }
    }
}
