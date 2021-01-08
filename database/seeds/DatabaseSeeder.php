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
       /* $this->call(UserSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(StateSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(PatientSeeder::class); */
    	$this->command->info("Welcome to Database Seeder!\n");

        $this->TruncateTable("App\Country");
        $this->CallSeeder("CountrysTableSeeder");

        $this->TruncateTable("App\City");
        $this->CallSeeder("CitiesTableSeeder");

        $this->TruncateTable("App\State");
        $this->CallSeeder("StatesTableSeeder");

        $this->TruncateTable("App\Speciality");
        $this->TruncateTable("App\SpecialityUsers");
        $this->CallSeeder("SpecialitiesTableSeeder");

    	$this->TruncateTable("App\User");
        $this->TruncateTable("App\Doctor");
        $this->TruncateTable("App\Patient");
        $this->TruncateTable("App\MentorDetail");
        $this->TruncateTable("App\EducationDetail");
        $this->TruncateTable("App\ExperienceDetail");
        $this->TruncateTable("App\AwardDetail");
    	$this->CallSeeder("UsersTableSeeder");

    	$this->TruncateTable("App\Setting");
    	$this->CallSeeder("SettingsTableSeeder");

    	$this->TruncateTable("App\SystemSetting");
    	$this->CallSeeder("SystemSettingsTableSeeder");

        $this->TruncateTable("App\TimeSchedule");
        $this->CallSeeder("TimeSchedulesTableSeeder");

        $this->TruncateTable("App\Service");
        $this->CallSeeder("ServicesTableSeeder");

        $this->TruncateTable("App\Appointment");
        $this->CallSeeder("AppointmentsTableSeeder");
    }

    private function CallSeeder($table)
	{
		// if ($this->command->confirm( "Confirm To Seed $table" )) {
			$this->call($table);
			$this->command->warn($table." Seeder Success\n");
		// }
	}

	private function TruncateTable($table)
	{
		// if ($this->command->confirm( "Confirm To Truncate $table" )) {
			DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Schema::disableForeignKeyConstraints();
			$table::truncate();
			DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Schema::enableForeignKeyConstraints();
			$this->command->warn($table." Truncated");
		// }
	}
}
