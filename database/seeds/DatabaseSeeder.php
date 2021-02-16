<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * php artisan db:seed --class=CountrysTableSeeder
     * @return void
     */
    public function run()
    {
        $this->call(TimezoneSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(StateSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PageMasterSeeder::class);
        $this->call(MultiLanguageSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(ScheduleTimingSeeder::class);
//        $this->call(AppointmentSeeder::class);
        $this->call(SpecialitySeeder::class);
        $this->call(PageContentSeeder::class);
    }

}
