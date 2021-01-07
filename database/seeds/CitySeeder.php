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
        //
        $data = [
			[1, 'Bombuflat', 1],
			[2, 'Garacharma', 1],
			[3, 'Port Blair', 2],
			[4, 'Rangat', 2],
			[5, 'Addanki', 3],
			[6, 'Adivivaram', 3],
			[7, 'Adoni', 4],
			[8, 'Aganampudi', 4],
			[9, 'Ajjaram', 5],
			[10, 'Akividu', 5],
			[11, 'Akkarampalle', 6],
			[12, 'Akkayapalle', 6],
			[13, 'Akkireddipalem', 7],
			[14, 'Alampur', 7],
			[15, 'Amalapuram', 8],
			[16, 'Amudalavalasa', 8],
			[17, 'Amur', 9],
			[18, 'Anakapalle', 9],
			[19, 'Anantapur', 10],
			[20, 'Andole', 10],
        ];
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Schema::disableForeignKeyConstraints();
		foreach ($data as $test) {
	        City::create([
	            'name' => $test[1],
	            'state_id' => $test[2],
	        ]);
	    }
		DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Schema::enableForeignKeyConstraints();
    }
}
