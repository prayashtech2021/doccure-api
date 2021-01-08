<?php

use Illuminate\Database\Seeder;
use App\TimeZone;


class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tz_all_list = timezone_identifiers_list();
        array_pop($tz_all_list); //remove 'UTC'

        foreach ($tz_all_list as $name) {
            $time = new \DateTime('now', new DateTimeZone($name));
            $offset = $time->format('P');

            $row = new TimeZone();
            $row->name = $name;
            $row->offset = $offset;
            $row->gmt = 'GMT('.$offset.') '. $name;
            $row->save();
        }
    }
}
