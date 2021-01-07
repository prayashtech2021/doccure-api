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
        //
        $data = [
    		[1, 'AF', 'Afghanistan', ''],
			[2, 'AL', 'Albania', ''],
			[3, 'DZ', 'Algeria', ''],
			[4, 'AS', 'American Samoa', ''],
			[5, 'AD', 'Andorra', ''],
        ];

        foreach ($data as $test) {
	        Country::create([
	            'sortname' => $test[1],
	            'name' => $test[2],
	            'tcode' => $test[3],
	        ]);
	    }
    }
}
